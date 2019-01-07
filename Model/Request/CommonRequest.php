<?php
/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Model\Request;

use HiPay\FullserviceMagento\Model\Cart\DeliveryInformation;
use HiPay\FullserviceMagento\Model\Config as HiPayConfig;
use HiPay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use HiPay\Fullservice\Gateway\Model\Cart\Cart as Cart;
use HiPay\Fullservice\Gateway\Model\Cart\Item as Item;
use HiPay\Fullservice\Enum\Cart\TypeItems;
use HiPay\Fullservice\Enum\Transaction\Operation;
use Magento\Setup\Exception;
use \HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory;

/**
 * Commmon Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class CommonRequest extends BaseRequest
{

    /**
     * FAKE DEFAULT PRODUCT CATEGORY
     * @deprecated
     *
     */
    const DEFAULT_PRODUCT_CATEGORY = 1;

    /**
     * Order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Payment Method
     *
     * @var \HiPay\Fullservice\Request\AbstractRequest
     */
    protected $_paymentMethod;

    protected $_ccTypes = array(
        'VI' => 'visa',
        'AE' => 'american-express',
        'MC' => 'mastercard',
        'MI' => 'maestro'
    );

    /**
     * @var \HiPay\FullserviceMagento\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var
     */
    protected $_cartFactory;

    /**
     * @var  \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @var
     */
    protected $_mappingCategoriesCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * {@inheritDoc}
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Model\Cart\CartFactory $cartFactory,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        CollectionFactory $mappingCategoriesCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        $params = []
    ) {
        parent::__construct(
            $logger,
            $checkoutData,
            $customerSession,
            $checkoutSession,
            $localeResolver,
            $requestFactory,
            $urlBuilder,
            $helper,
            $params
        );

        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_mappingCategoriesCollectionFactory = $mappingCategoriesCollectionFactory;
        $this->_categoryFactory = $categoryFactory;

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Order instance is required.'));
        }

        if (isset($params['paymentMethod'])
            && $params['paymentMethod'] instanceof \HiPay\Fullservice\Request\AbstractRequest
        ) {
            $this->_paymentMethod = $params['paymentMethod'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Object Request PaymentMethod instance is required.')
            );
        }
    }

    /**
     *  Escape Html String to json embed
     *
     * @param string $string
     * @return string
     */
    private function escapeHtmlToJson($string)
    {
        return str_ireplace("'", "&apos;", $string);
    }

    /**
     *  Build an Cart Json
     *
     * @param null $operation
     * @param bool $useOrderCurrency
     * @return mixed
     * @throws \Exception
     */
    protected function processCartFromOrder($operation = null, $useOrderCurrency = false)
    {
        $cartFactory = $this->_cartFactory->create(
            [
                'salesModel' => $this->_order,
                'operation' => $operation,
                'payment' => $this->_order->getPayment()
            ]
        );

        $cart = new Cart();
        $items = $cartFactory->getAllItems($useOrderCurrency);
        foreach ($items as $item) {
            $itemHipay = null;
            $reference = $item->getDataUsingMethod('reference');
            $name = $item->getDataUsingMethod('name');
            $amount = $item->getDataUsingMethod('amount');
            $price = $item->getDataUsingMethod('price');
            $taxPercent = $item->getDataUsingMethod('tax_percent');
            $qty = $item->getDataUsingMethod('qty');
            $discount = $item->getDataUsingMethod('discount');

            /** @var \HiPay\Fullservice\Gateway\Model\Cart\Item */
            switch ($item->getType()) {
                case TypeItems::GOOD:
                    $product = $this->_productRepositoryInterface->get($reference);
                    $description = $product->getCustomAttribute('description');
                    $itemHipay = new Item();
                    $itemHipay->setName($name);
                    $itemHipay->setProductReference($reference);
                    $itemHipay->setType(TypeItems::GOOD);
                    $itemHipay->setQuantity($qty);
                    $itemHipay->setUnitPrice($price);
                    $itemHipay->setTaxRate($taxPercent);
                    $itemHipay->setDiscount($discount);
                    $itemHipay->setTotalAmount($amount);
                    $itemHipay->setProductDescription($this->escapeHtmlToJson($description->getValue()));
                    $itemHipay->setProductCategory($this->getMappingCategory($product));

                    // Set Specifics informations as EAN
                    if (!empty($this->_config->getEanAttribute())) {
                        $ean = $product->getCustomAttribute($this->_config->getEanAttribute());
                        $itemHipay->setEuropeanArticleNumbering($ean);
                    }
                    break;
                case TypeItems::DISCOUNT:
                    $itemHipay = Item::buildItemTypeDiscount(
                        $reference,
                        $name,
                        0,
                        0,
                        $taxPercent,
                        $name . ' Total discount :' . $amount,
                        0
                    );
                    $itemHipay->setProductCategory(self::DEFAULT_PRODUCT_CATEGORY);
                    break;
                case TypeItems::FEE:
                    if (in_array($operation, array(Operation::REFUND, Operation::CAPTURE)) && $amount == 0) {
                        break;
                    }
                    $itemHipay = Item::buildItemTypeFees(
                        $reference,
                        $name,
                        $amount,
                        $taxPercent,
                        $discount,
                        $amount
                    );
                    $itemHipay->setProductCategory(self::DEFAULT_PRODUCT_CATEGORY);
                    break;

            }

            if ($itemHipay) {
                $cart->addItem($itemHipay);
            }
        }

        if (!$cartFactory->isAmountAvailable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Amount for line items is not correct.'));
        }

        return $cart->toJson();
    }

    /**
     *  Get mapping from Magento category for Hipay compliance
     *
     * @param $product
     * @return int|null code category Hipay
     */
    protected function getMappingCategory($product)
    {
        $mapping_id = null;
        $categories = $product->getCategoryIds();
        if (!empty($categories) && !empty($idCategory = $categories[0])) {
            $mappingNotFound = true;
            while ($mappingNotFound) {
                $collection = $this->_mappingCategoriesCollectionFactory->create()
                    ->addFieldToFilter('category_magento_id', $idCategory)
                    ->load();

                // Mapping is on the First Level
                if ($collection->getItems()) {
                    $mapping_id = (int)$collection->getFirstItem()->getId();
                    break;
                }
                // Check if mapping exist with parent // Stop when parent is 1 (ROOT CATEGORIES)
                $category = $this->_categoryFactory->create();
                $category->getResource()->load($category, $idCategory);
                $parentId = $category->getParentId();
                if ($parentId === null || $parentId == 1) {
                    break;
                }
                $category = $this->_categoryFactory->create();
                $category->getResource()->load($category, $parentId);
                $idCategory = $category->getId();
            }
        }
        return $mapping_id;
    }
}
