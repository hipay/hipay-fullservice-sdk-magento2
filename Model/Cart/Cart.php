<?php

namespace HiPay\FullserviceMagento\Model\Cart;

use HiPay\Fullservice\Enum\Cart\TypeItems;
use HiPay\Fullservice\Enum\Transaction\Operation;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\Cart\SalesModel\Factory;
use Magento\Payment\Model\Cart\SalesModel\SalesModelInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Weee\Helper\Data;

/**
 * Cart model
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Cart extends \Magento\Payment\Model\Cart
{
    protected const GENERIC_DISCOUNT = 'Discount';

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var string
     */
    protected $_operation;

    /**
     * @var Payment
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
     * @param Factory             $salesModelFactory
     * @param ManagerInterface    $eventManager
     * @param Data                $weeeHelper
     * @param SalesModelInterface $salesModel
     * @param string              $operation
     * @param Payment             $payment
     */
    public function __construct(
        \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Weee\Helper\Data $weeeHelper,
        SalesModelInterface $salesModel,
        string $operation,
        Payment $payment
    ) {
        $this->_eventManager = $eventManager;
        $this->_salesModel = $salesModelFactory->create($salesModel);
        $this->weeeHelper = $weeeHelper;
        $this->_operation = $operation;
        $this->_payment = $payment;
        $this->_model = $this->_salesModel;

        if ($this->_operation == Operation::REFUND) {
            $this->_model = $this->_payment->getCreditMemo();
        } else {
            if ($this->_operation == Operation::CAPTURE) {
                if ($this->_payment->getOrder()->hasInvoices()) {
                    $this->_model = $this->_payment->getOrder()->getInvoiceCollection()->getLastItem();
                }
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
     * @param bool $useOrderCurrency
     */
    protected function _calculateCustomItemsSubtotal($useOrderCurrency = false)
    {
        if (
            $this->_salesModel->getTaxContainer()->getShippingInvoiced() == null
            || $this->_salesModel->getTaxContainer()->getShippingRefunded() > 0
        ) {
            $this->_processShippingAndDiscountItems($useOrderCurrency);
        }
        $this->_applyDiscountTaxCompensationWorkaround($this->_salesModel, $useOrderCurrency);
        $this->_validate($useOrderCurrency);
    }

    /**
     * Calculate subtotal from shipping and discount items
     *
     * @param bool $useOrderCurrency
     */
    protected function _processShippingAndDiscountItems($useOrderCurrency = false)
    {
        if (
            $this->_operation != Operation::REFUND
            && $this->_operation != Operation::CAPTURE
            && $this->getDiscount()
        ) {
            $reference = self::GENERIC_DISCOUNT;
            $description = self::GENERIC_DISCOUNT;

            if (!empty($this->_salesModel->getDataUsingMethod('coupon_code'))) {
                $reference = $this->_salesModel->getDataUsingMethod('coupon_code');
                $description = $this->_salesModel->getDataUsingMethod('discount_description');
            }

            $this->addGenericItem(
                $description,
                -1.00 * $this->getDiscount(),
                $reference,
                $description,
                0,
                TypeItems::DISCOUNT
            );
        }
        $base_shipping = 0;
        $tax_rate = 0;
        if (!empty($this->_salesModel->getDataUsingMethod('shipping_method'))) {
            $shippingAmount = (float)$this->_model->getDataUsingMethod('base_shipping_incl_tax');
            if ($useOrderCurrency) {
                $shippingAmount = (float)$this->_model->getDataUsingMethod('shipping_incl_tax');
            }
            if ($shippingAmount > 0) {
                $base_shipping = round($shippingAmount, 3);
                if ($useOrderCurrency) {
                    $tax_rate = round(
                        $this->_model->getDataUsingMethod(
                            'shipping_tax_amount'
                        ) / $this->_model->getDataUsingMethod(
                            'shipping_incl_tax'
                        ) * 100,
                        2
                    );
                } else {
                    $tax_rate = round(
                        $this->_model->getDataUsingMethod(
                            'base_shipping_tax_amount'
                        ) / $this->_model->getDataUsingMethod(
                            'base_shipping_incl_tax'
                        ) * 100,
                        2
                    );
                }
            }
            $this->addGenericItem(
                $this->_salesModel->getDataUsingMethod('shipping_description'),
                $base_shipping,
                $this->_salesModel->getDataUsingMethod('shipping_method'),
                $this->_salesModel->getDataUsingMethod('shipping_description'),
                $tax_rate,
                TypeItems::FEE
            );
        }
    }

    /**
     * Add Shipping and fee Item
     *
     * @param string $name
     * @param float  $amount
     * @param string $reference
     * @param string $description
     * @param string $taxPercent
     * @param string $type
     * @return void
     * @api
     */
    public function addGenericItem($name, $amount, $reference, $description, $taxPercent, $type)
    {
        $this->_customItems[] = $this->_createItemHipayFromData(
            $name,
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
     * @param bool $useOrderCurrency
     */
    protected function _validate($useOrderCurrency = false)
    {
        $this->_areAmountsValid = true;
        if ($useOrderCurrency) {
            $referenceAmount = $this->_model->getDataUsingMethod('grand_total');
        } else {
            $referenceAmount = $this->_model->getDataUsingMethod('base_grand_total');
        }
        $itemsSubtotal = 0;
        foreach ($this->_salesModelItems as $item) {
            $itemsSubtotal = $itemsSubtotal + $item->getAmount();
        }
        $sum = $itemsSubtotal + $this->getShipping();

        if (sprintf('%.4F', $sum) != sprintf('%.4F', $referenceAmount)) {
            $delta = (float)$referenceAmount - $sum;
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
     * Get all cart items
     *
     * @param  bool $useOrderCurrency
     * @return array
     * @api
     */
    public function getAllItems($useOrderCurrency = false)
    {
        $this->_collectItemsAndAmounts($useOrderCurrency);
        return array_merge($this->_salesModelItems, $this->_customItems);
    }

    /**
     * Collect all items, discounts, taxes, shipping to cart
     *
     * @param  bool $useOrderCurrency
     * @return void
     */
    protected function _collectItemsAndAmounts($useOrderCurrency = false)
    {
        if (!$this->_itemsCollectingRequired) {
            return;
        }

        $this->_itemsCollectingRequired = false;

        $this->_salesModelItems = [];
        $this->_customItems = [];

        $this->_resetAmounts();

        $this->_eventManager->dispatch('payment_cart_collect_items_and_amounts', ['cart' => $this]);

        $this->_importItemsFromSalesModel($useOrderCurrency);
        $this->_calculateCustomItemsSubtotal($useOrderCurrency);
    }

    /**
     * Import items and convert to HiPays format
     *
     * @param  bool $useOrderCurrency
     * @return void
     */
    protected function _importItemsFromSalesModel($useOrderCurrency = false)
    {
        $this->_salesModelItems = [];
        $items = $this->_model->getAllItems();
        if ($useOrderCurrency) {
            $totalTax = $this->_model->getDataUsingMethod('tax_amount');
            $totalSubTotal = $this->_model->getDataUsingMethod('subtotal');
            $totalDiscount = $this->_model->getDataUsingMethod('discount_amount');
            $totalShipping = $this->_model->getDataUsingMethod('shipping_incl_tax');
        } else {
            $totalTax = $this->_model->getBaseTaxAmount();
            $totalSubTotal = $this->_model->getBaseSubtotal();
            $totalDiscount = $this->_model->getBaseDiscountAmount();
            $totalShipping = $this->_model->getDataUsingMethod('base_shipping_incl_tax');
        }

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $originalItem = $item->getOriginalItem();
            switch ($this->_operation) {
                case Operation::REFUND:
                    $originalItem = $item;
                    break;
                case Operation::CAPTURE:
                    $originalItem = $item;
                    break;
            }

            if (
                $this->_operation != null
                && ($this->_operation == Operation::CAPTURE || $this->_operation == Operation::REFUND)
            ) {
                $qty = (int)$originalItem->getData('qty');
            } else {
                $qty = (int)$originalItem->getData('qty_ordered');
            }

            $sku = $originalItem->getSku();
            $productId = $originalItem->getProductId();
            $description = $originalItem->getDescription();
            $taxPercent = $originalItem->getTaxPercent();
            if ($useOrderCurrency) {
                $price = $originalItem->getPriceInclTax();
                $discount = $originalItem->getDiscountAmount();
            } else {
                $price = $originalItem->getBasePriceInclTax();
                $discount = $originalItem->getBaseDiscountAmount();
            }

            //HiPay needs total amount with 3 decimals to match the correct total amount within 1 cent
            /**
             * @see Magento\Weee\Block\Item\Price
             */
            $itemTotalInclTax = $this->getTotalPrice($originalItem, $useOrderCurrency);

            // Need better precision and unit price with reel tax application
            if (
                $this->_operation != null
                && ($this->_operation == Operation::CAPTURE
                || $this->_operation == Operation::REFUND)
            ) {
                // To avoid 0.001 between original authorization and capture and refund
                foreach ($this->_salesModel->getAllItems() as $key => $orderItem) {
                    if ($orderItem->getParentItem()) {
                        continue;
                    }

                    if ($originalItem->getSku() == $orderItem->getOriginalItem()->getSku()) {
                        if ($useOrderCurrency) {
                            $discountOrder = $orderItem->getOriginalItem()->getDiscountAmount();
                        } else {
                            $discountOrder = $orderItem->getOriginalItem()->getBaseDiscountAmount();
                        }

                        $price = $this->returnUnitPrice(
                            $this->getTotalPrice($orderItem->getOriginalItem(), $useOrderCurrency) + $discountOrder,
                            $orderItem->getOriginalItem()->getData('qty_ordered')
                        );

                        // Adjust discount to avoid 0.01 difference
                        $discount = round(($price * $qty) - $itemTotalInclTax, 3);
                    }
                }
            } else {
                $price = $this->returnUnitPrice($itemTotalInclTax + $discount, $qty);
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
                    TypeItems::GOOD,
                    $productId
                );
            }
        }

        $this->addSubtotal($totalSubTotal);
        $this->addTax($totalTax);
        $this->addShipping($totalShipping);
        $this->addDiscount(abs($totalDiscount));
    }

    /**
     * Calculate the total item price including tax
     *
     * @param  Item|\Magento\Sales\Model\Order\Invoice\Item|\Magento\Sales\Model\Order\Creditmemo\Item $originalItem
     * @param  bool                                                                                    $useOrderCurrency
     * @return mixed
     */
    private function getTotalPrice($originalItem, $useOrderCurrency = false)
    {
        if ($useOrderCurrency) {
            $totalPrice = $originalItem->getRowTotal()
                - $originalItem->getDiscountAmount()
                + $originalItem->getDiscountTaxCompensationAmount()
                + $this->weeeHelper->getRowWeeeTaxInclTax($originalItem) + $originalItem->getTaxAmount();
        } else {
            $totalPrice = $originalItem->getBaseRowTotal()
                - $originalItem->getBaseDiscountAmount()
                + $originalItem->getBaseDiscountTaxCompensationAmount()
                + $this->weeeHelper->getBaseRowWeeeTaxInclTax($originalItem) + $originalItem->getBaseTaxAmount();
        }

        return $totalPrice;
    }

    /**
     * Create item object from item data
     *
     * @param string $name
     * @param int    $qty
     * @param float  $amount
     * @param float  $price
     * @param float  $sku
     * @param string $description
     * @param float  $taxPercent
     * @param float  $discount
     * @param string $type
     * @param string $productId
     * @return \Magento\Framework\DataObject
     */
    protected function _createItemHipayFromData(
        $name,
        $qty,
        $amount,
        $price,
        $sku,
        $description,
        $taxPercent,
        $discount,
        $type,
        $productId = null
    ) {
        $item = new \Magento\Framework\DataObject(
            [
                'name' => $name,
                'qty' => $qty,
                'amount' => $amount,
                'price' => $price,
                'reference' => $sku,
                'description' => $description,
                'tax_percent' => $taxPercent,
                'discount' => $discount,
                'type' => $type,
                'product_id' => $productId
            ]
        );

        return $item;
    }

    /**
     * Add "hidden" discount and shipping tax
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
     * @param SalesModelInterface $salesEntity
     * @param bool                $useOrderCurrency
     */
    protected function _applyDiscountTaxCompensationWorkaround(
        SalesModelInterface $salesEntity,
        $useOrderCurrency = false
    ) {
        $dataContainer = $salesEntity->getTaxContainer();
        if ($useOrderCurrency) {
            $this->addTax((double)$dataContainer->getDiscountTaxCompensationAmount());
            $this->addTax((double)$dataContainer->getShippingDiscountTaxCompensationAmnt());
        } else {
            $this->addTax((double)$dataContainer->getBaseDiscountTaxCompensationAmount());
            $this->addTax((double)$dataContainer->getBaseShippingDiscountTaxCompensationAmnt());
        }
    }

    /**
     * Calculate unit price for one product and quantity ( Get better precision )
     *
     * @param  float|mixed|null $itemTotalInclTax
     * @param  int              $qty
     * @return float
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
