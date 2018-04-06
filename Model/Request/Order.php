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

use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\Fullservice\Enum\Customer\Gender;
use HiPay\FullserviceMagento\Model\Request\CommonRequest as CommonRequest;

/**
 * Order Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Order extends CommonRequest
{

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
     *  Operation type
     *
     * @var string $operation
     */
    protected $_operation = null;

    /**
     * Customer Factory
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_customerRepositoryInterface;

    /**
     *
     * @var  \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepositoryInterface;

    /**
     * {@inheritDoc}
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\Url $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Model\Cart\CartFactory $cartFactory,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory $mappingCategoriesCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepositoryInterface,
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
            $cartFactory,
            $weeeHelper,
            $productRepositoryInterface,
            $mappingCategoriesCollectionFactory,
            $categoryFactory,
            $params
        );

        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_groupRepositoryInterface = $groupRepositoryInterface;

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Exception('Order instance is required.');
        }

        if (isset($params['operation'])) {
            $this->_operation = $params['operation'];
        } else {
        }

        if (isset($params['paymentMethod']) && $params['paymentMethod'] instanceof \HiPay\Fullservice\Request\AbstractRequest) {
            $this->_paymentMethod = $params['paymentMethod'];
        } else {
            throw new \Exception('Object Request PaymentMethod instance is required.');
        }
    }

    protected function getCcTypeHipay($mageCcType)
    {
        $hipayCcType = $mageCcType;
        if (in_array($mageCcType, array_keys($this->_ccTypes))) {
            $hipayCcType = $this->_ccTypes[$mageCcType];
        }
        return $hipayCcType;
    }

    /**
     * Check if requested ECI is MO/TO
     * @return bool
     */
    protected function isMOTO()
    {
        $eci = $this->_order->getForcedEci() ?: $this->_order->getPayment()->getAdditionalInformation('eci');
        if ($eci == \HiPay\Fullservice\Enum\Transaction\ECI::MOTO) {
            return true;
        }

        return false;
    }

    /**
     *  Some payments method need product code with fees or no fees
     *
     * @return string|bool
     */
    private function getPaymentProductFees()
    {
        $payment_fees = $this->_config->getValue('payment_product_fees');
        if (!empty($payment_fees)) {
            return $payment_fees;
        }
        return false;
    }

    /**
     *  Return payment product
     *
     *  If Payment requires specified option ( With Fees or without Fees return it otherwhise normal payment product)
     *
     * @return string
     */
    private function getSpecifiedPaymentProduct()
    {
        return ($this->getPaymentProductFees()) ? $this->getPaymentProductFees() :
            $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_products');
    }


    /**
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    protected function mapRequest()
    {
        $payment_product = $this->getSpecifiedPaymentProduct();
        $useOrderCurrency = $this->_config->useOrderCurrency();

        $orderRequest = new OrderRequest();
        $orderRequest->orderid = $this->_order->getForcedOrderId() ?: $this->_order->getIncrementId();
        $orderRequest->operation = $this->_order->getForcedOperation() ?: $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_action');
        $orderRequest->payment_product = $this->getCcTypeHipay($this->_order->getPayment()->getCcType()) ?: $payment_product;
        $orderRequest->description = $this->_order->getForcedDescription() ?: sprintf("Order %s",
            $this->_order->getIncrementId()); //@TODO
        $orderRequest->long_description = "";
        if ($useOrderCurrency) {
            $orderRequest->currency = $this->_order->getOrderCurrencyCode();
            $orderRequest->amount = $this->_order->getForcedAmount() ?: (float)$this->_order->getGrandTotal();
            $orderRequest->shipping = (float)$this->_order->getShippingAmount();
            $orderRequest->tax = (float)$this->_order->getTaxAmount();
        } else {
            $orderRequest->currency = $this->_order->getBaseCurrencyCode();
            $orderRequest->amount = $this->_order->getForcedAmount() ?: (float)$this->_order->getBaseGrandTotal();
            $orderRequest->shipping = (float)$this->_order->getBaseShippingAmount();
            $orderRequest->tax = (float)$this->_order->getBaseTaxAmount();
        }

        $orderRequest->cid = $this->_customerId;
        $orderRequest->ipaddr = $this->_order->getRemoteIp();
        $orderRequest->language = $this->_localeResolver->getLocale();

        $redirectParams = ['_secure' => true];
        if ($this->isMOTO()) {
            $redirectParams['is_moto'] = true;
        }

        // URL callback
        $orderRequest->accept_url = $this->_urlBuilder->getUrl('hipay/redirect/accept', $redirectParams);
        $orderRequest->pending_url = $this->_urlBuilder->getUrl('hipay/redirect/pending', $redirectParams);
        $orderRequest->decline_url = $this->_urlBuilder->getUrl('hipay/redirect/decline', $redirectParams);
        $orderRequest->cancel_url = $this->_urlBuilder->getUrl('hipay/redirect/cancel', $redirectParams);
        $orderRequest->exception_url = $this->_urlBuilder->getUrl('hipay/redirect/exception', $redirectParams);

        if ($this->_config->isSendingNotifyUrl()) {
            $orderRequest->notify_url = $this->_urlBuilder->getUrl("hipay/notify/index");
        }

        $orderRequest->paymentMethod = $this->_paymentMethod;
        $orderRequest->customerBillingInfo = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\Info\BillingInfo',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();
        $orderRequest->customerShippingInfo = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        // Extras informations
        $this->processExtraInformations($orderRequest, $useOrderCurrency);

        return $orderRequest;
    }

    /**
     *  Process all extras information for the request
     *
     * @param OrderRequest $order OrderRequest passed by reference
     */
    private function processExtraInformations(OrderRequest &$orderRequest, $useOrderCurrency = false)
    {
        // Check if fingerprint is enabled
        if ($this->_config->isFingerprintEnabled()) {
            $orderRequest->device_fingerprint = $this->_order->getPayment()->getAdditionalInformation('fingerprint');
        }

        // Check if sending cart is necessary ( If  conf enabled or if payment method product needs it )
        if ($this->_config->isNecessaryToSendCartItems($orderRequest->payment_product)) {
            $orderRequest->basket = $this->processCartFromOrder($this->_operation, $useOrderCurrency);
        }

        // Check if delivery method is required for the payment method
        if ($this->_config->isDeliveryMethodRequired($orderRequest->payment_product)) {
            $orderRequest->delivery_information = $this->_requestFactory->create('\HiPay\FullserviceMagento\Model\Request\Info\DeliveryInfo',
                ['params' => ['order' => $this->_order, 'config' => $this->_config]])->getRequestObject();
        }

        // Technical parameter to track wich magento version is used
        $orderRequest->source = $this->helper->getRequestSource();

        /*
         *  Custom Data
         *
         * You can use these parameters to submit custom values you wish to show in HiPay back office transaction details,
         * receive back in the API response messages, in the notifications or to activate specific FPS rules.
         *
         *  Please make an Magento 2 plugin which listen the method "getCustomData"
         *  of the class "HiPay\FullserviceMagento\Helper\Data"
         */
        $customData = $this->getCustomData();
        if (!empty($customData)) {
            $orderRequest->custom_data = json_encode($customData);
        }

        /*
         * Override or format mapping informations for specific provider
         */
        if ($orderRequest->payment_product == 'bnpp-3xcb' || $orderRequest->payment_product == 'bnpp-4xcb') {
            $orderRequest->customerBillingInfo->phone = preg_replace('/^(\+33)|(33)/', '0',
                $orderRequest->customerBillingInfo->phone);

            if ($orderRequest->customerBillingInfo->gender == null) {
                $orderRequest->customerBillingInfo->gender = Gender::MALE;
            }
        }

    }


    /**
     *  Generate custom data to send to HiPay back office
     *
     * @return array
     */
    public function getCustomData()
    {
        $customData = array();
        return $customData;
    }

    /**
     * @return \Magento\Sales\Model\Order|mixed
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterface|\Magento\Sales\Model\Order
     */
    public function getCustomerRepositoryInterface()
    {
        return $this->_customerRepositoryInterface;
    }

    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterface|\Magento\Sales\Model\Order
     */
    public function getGroupRepositoryInterface()
    {
        return $this->_groupRepositoryInterface;
    }
}
