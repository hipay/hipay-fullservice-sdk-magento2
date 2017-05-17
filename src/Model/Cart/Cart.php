<?php

namespace HiPay\FullserviceMagento\Model\Cart;


use HiPay\Fullservice\Enum\Cart\TypeItems;
use HiPay\Fullservice\Enum\Transaction\Operation;

/**
 * Cart model
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Cart extends \Magento\Payment\Model\Cart
{
    const GENERIC_DISCOUNT = 'Discount';

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var string
     */
    protected $_operation;

    /**
     * @var string
     */
    protected $_payment;


    /**
     *  Sales Model for Invoice or CreditMemo
     *
     * @var string
     */
    protected $_model;

    /**
     * @var bool
     */
    protected $_areAmountsValid = false;

    /**
     * @param \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\Data\CartInterface $salesModel
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param string $operation
     * @param string $payment
     */
    public function __construct(
        \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Weee\Helper\Data $weeeHelper,
        $salesModel,
        $operation,
        $payment
    )
    {
        $this->_eventManager = $eventManager;
        $this->_salesModel = $salesModelFactory->create($salesModel);
        $this->weeeHelper = $weeeHelper;
        $this->_operation = $operation;
        $this->_payment = $payment;
        $this->_model = $this->_salesModel;

        if ($this->_operation == Operation::REFUND) {
            $this->_model = $this->_payment->getCreditMemo();
        } else if ($this->_operation == Operation::CAPTURE) {
            if ($this->_payment->getOrder()->hasInvoices()) {
                $this->_model = $this->_payment->getOrder()->getInvoiceCollection()->getLastItem();
            }
        }

        $this->_resetAmounts();
    }

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @return array
     */
    public function getAmounts()
    {
        $this->_collectItemsAndAmounts();

        if (!$this->_areAmountsValid) {
            $subtotal = $this->getSubtotal() + $this->getTax();

            if (empty($this->_transferFlags[self::AMOUNT_SHIPPING])) {
                $subtotal += $this->getShipping();
            }

            if (empty($this->_transferFlags[self::AMOUNT_DISCOUNT])) {
                $subtotal -= $this->getDiscount();
            }

            return [self::AMOUNT_SUBTOTAL => $subtotal];
        }

        return $this->_amounts;
    }

    /**
     * Calculate subtotal from custom items
     *
     * @return void
     */
    protected function _calculateCustomItemsSubtotal()
    {
        $this->_processShippingAndDiscountItems();
        $this->_applyDiscountTaxCompensationWorkaround($this->_salesModel);
        $this->_validate();
    }

    /**
     * Calculate subtotal from shipping and discount items
     *
     * @return void
     */
    protected function _processShippingAndDiscountItems()
    {
        if ($this->_operation != Operation::REFUND &&
                $this->_operation != Operation::CAPTURE &&
                     $this->getDiscount()) {
            $reference = self::GENERIC_DISCOUNT;
            $description = self::GENERIC_DISCOUNT;

            if (!empty($this->_salesModel->getDataUsingMethod('coupon_code'))) {
                $reference = $this->_salesModel->getDataUsingMethod('coupon_code');
                $description = $this->_salesModel->getDataUsingMethod('discount_description');
            }

            $this->addGenericItem($description,
                -1.00 * $this->getDiscount(),
                $reference,
                $description,
                0,
                TypeItems::DISCOUNT);

        }

        if ($this->getShipping()) {
            if ((int) $this->_model->getDataUsingMethod('base_shipping_incl_tax') > 0) {
                $this->addGenericItem($this->_salesModel->getDataUsingMethod('shipping_description'),
                    $this->_model->getDataUsingMethod('base_shipping_incl_tax'),
                    $this->_salesModel->getDataUsingMethod('shipping_method'),
                    $this->_salesModel->getDataUsingMethod('shipping_description'),
                    round($this->_model->getDataUsingMethod('base_shipping_tax_amount') / $this->_model->getDataUsingMethod('base_shipping_incl_tax') * 100, 2),
                    TypeItems::FEE);
            }

        }
    }

    /**
     * Add Shipping and fee Item
     *
     * @param string $name
     * @param float $amount
     * @param string $reference
     * @param string $description
     * @param string $taxPercent
     * @param string $type
     * @return void
     * @api
     */
    public function addGenericItem($name, $amount, $reference, $description, $taxPercent, $type)
    {
        $this->_customItems[] = $this->_createItemHipayFromData($name,
            1,
            $amount,
            $amount,
            $reference,
            $description,
            $taxPercent,
            0,
            $type
        );
    }

    /**
     * Check line items and totals
     *
     *
     * @return void
     */
    protected function _validate()
    {
        $this->_areAmountsValid = true;
        $referenceAmount = $this->_model->getDataUsingMethod('base_grand_total');
        $itemsSubtotal = 0;
        foreach ($this->_salesModelItems as $item) {
            $itemsSubtotal = $itemsSubtotal + $item->getAmount();
        }
        $sum = $itemsSubtotal + $this->getShipping();

        if (sprintf('%.4F', $sum) != sprintf('%.4F', $referenceAmount)) {
            $delta = (float) $referenceAmount - $sum;
            if ($delta < 0.02) {
                // Bug with 1 cent on specific shipping method (For example UPS)
                foreach ($this->_customItems as $item) {
                    if ($item->getType() == TypeItems::FEE) {
                        $item->setAmount($item->getAmount() + round($delta, 2));
                    }
                }
            } else {
                $this->_areAmountsValid = false;
            }
        }
    }

    /**
     * Import items and convert to HiPays format
     *
     * @return void
     */
    protected function _importItemsFromSalesModel()
    {
        $this->_salesModelItems = [];
        $items = $this->_model->getAllItems();
        $totalSubTotal = $this->_model->getBaseSubtotal();
        $totalTax = $this->_model->getBaseTaxAmount();
        $totalShipping = $this->_model->getDataUsingMethod('base_shipping_incl_tax');
        $totalDiscount = $this->_model->getBaseDiscountAmount();

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $originalItem = $item->getOriginalItem();
            switch ($this->_operation) {
                case Operation::REFUND :
                    $originalItem = $item;
                    break;
                case Operation::CAPTURE:
                    $originalItem = $item;
                    break;
            }

            if ($this->_operation != null && ($this->_operation == Operation::CAPTURE || $this->_operation == Operation::REFUND)) {
                $qty = intval($originalItem->getData('qty'));
            } else {
                $qty = intval($originalItem->getData('qty_ordered'));
            }

            $price = $originalItem->getBasePriceInclTax();
            $sku = $originalItem->getSku();
            $description = $originalItem->getDescription();
            $taxPercent = $originalItem->getTaxPercent();
            $discount = $originalItem->getBaseDiscountAmount();

            //HiPay needs total amount with 3 decimals to match the correct total amount within 1 cent
            //@see Magento\Weee\Block\Item\Price
            $itemTotalInclTax = $this->getTotalPrice($originalItem);

            // Need better precision and unit price with reel tax application
            if ($price * $qty != $itemTotalInclTax) {
                if ($this->_operation != null && ($this->_operation == Operation::CAPTURE || $this->_operation == Operation::REFUND)) {
                    // To avoid 0.001 between original authorization and capture and refund
                    foreach ($this->_salesModel->getAllItems() as $key => $orderItem) {
                        if ($orderItem->getParentItem()) {
                            continue;
                        }

                        if ($originalItem->getSku() == $orderItem->getOriginalItem()->getSku()) {
                            $discountOrder = $orderItem->getOriginalItem()->getBaseDiscountAmount();
                            $price = $this->returnUnitPrice($this->getTotalPrice($orderItem->getOriginalItem()) + $discountOrder, $orderItem->getOriginalItem()->getData('qty_ordered'));

                            // Adjust discount to avoid 0.01 difference
                            $discount = ($price * $qty) - $itemTotalInclTax;
                        }
                    }
                } else {
                    $price = $this->returnUnitPrice($itemTotalInclTax + $discount, $qty);
                }
            }

            // Add an item only if its calculated item
            if ($itemTotalInclTax > 0) {
                $this->_salesModelItems[] = $this->_createItemHipayFromData(
                    $originalItem->getName(),
                    $qty,
                    $itemTotalInclTax,
                    $price,
                    $sku,
                    $description,
                    $taxPercent,
                    -1.00 * $discount,
                    TypeItems::GOOD
                );
            }
        }

        $this->addSubtotal($totalSubTotal);
        $this->addTax($totalTax);
        $this->addShipping($totalShipping);
        $this->addDiscount(abs($totalDiscount));
    }

    /**
     * @param $originalItem
     * @return float
     */
    private function getTotalPrice($originalItem)
    {
        return $originalItem->getBaseRowTotal()
            - $originalItem->getBaseDiscountAmount()
            + $originalItem->getBaseDiscountTaxCompensationAmount()
            + $this->weeeHelper->getBaseRowWeeeTaxInclTax($originalItem) + $originalItem->getBaseTaxAmount();
    }

    /**
     * Create item object from item data
     *
     * @param string $name
     * @param int $qty
     * @param float $amount
     * @param float $price
     * @param float $sku
     * @param string $description
     * @param float $taxPercent
     * @param float discount
     * @param string type
     * @return \Magento\Framework\DataObject
     */
    protected function _createItemHipayFromData($name, $qty, $amount, $price, $sku, $description, $taxPercent, $discount, $type)
    {
        $item = new \Magento\Framework\DataObject(['name' => $name,
            'qty' => $qty,
            'amount' => $amount,
            'price' => $price,
            'reference' => $sku,
            'description' => $description,
            'tax_percent' => $taxPercent,
            'discount' => $discount,
            'type' => $type]);

        return $item;
    }

    /**
     * Add "hidden" discount and shipping tax
     *
     * Go ahead, try to understand ]:->
     *
     * Tax settings for getting "discount tax":
     * - Catalog Prices = Including Tax
     * - Apply Customer Tax = After Discount
     * - Apply Discount on Prices = Including Tax
     *
     * Test case for getting "hidden shipping tax":
     * - Make sure shipping is taxable (set shipping tax class)
     * - Catalog Prices = Including Tax
     * - Shipping Prices = Including Tax
     * - Apply Customer Tax = After Discount
     * - Create a cart price rule with % discount applied to the Shipping Amount
     * - run shopping cart and estimate shipping
     * - go to PayPal
     *
     * @param \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface $salesEntity
     * @return void
     */
    protected function _applyDiscountTaxCompensationWorkaround(
        \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface $salesEntity
    )
    {
        $dataContainer = $salesEntity->getTaxContainer();
        $this->addTax((double)$dataContainer->getBaseDiscountTaxCompensationAmount());
        $this->addTax((double)$dataContainer->getBaseShippingDiscountTaxCompensationAmnt());
    }

    /*
     *  Calculate unit price for one product and quantity ( Get better precision )
     *
     *@param $product
     *@param $quantity
     */
    private function returnUnitPrice($itemTotalInclTax, $qty)
    {
        $price = $itemTotalInclTax / $qty;
        return round($price, 3);
    }

    /**
     * Check whether any item has negative amount
     *
     * @return bool
     */
    public function hasNegativeItemAmount()
    {
        foreach ($this->_customItems as $item) {
            if ($item->getAmount() < 0) {
                return true;
            }
        }
        return false;
    }


    /**
     *  Is Cart Ok for sending in transaction
     *
     * @return bool
     */
    public function isAmountAvailable()
    {
        return $this->_areAmountsValid;
    }

}
