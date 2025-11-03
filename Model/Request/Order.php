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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request;

use HiPay\Fullservice\Enum\ThreeDSTwo\DeviceChannel;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\Fullservice\Enum\Customer\Gender;
use HiPay\FullserviceMagento\Model\Cart\CartFactory;
use HiPay\FullserviceMagento\Model\Request\CommonRequest as CommonRequest;
use HiPay\FullserviceMagento\Model\Request\Type\Factory;
use HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory;
use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\FullserviceMagento\Model\System\Config\Source\OrderExpirationTimes;
use HiPay\FullserviceMagento\Model\Method\HostedFieldsMethod;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Order Request Object
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Order extends CommonRequest
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \HiPay\Fullservice\Request\AbstractRequest
     */
    protected $_paymentMethod;

    /**
     * @var string[]
     */
    protected $_ccTypes = [
        'VI' => 'visa',
        'AE' => 'american-express',
        'MC' => 'mastercard',
        'MI' => 'maestro',
        'visa' => 'visa',
        'american-express' => 'american-express',
        'mastercard' => 'mastercard',
        'cb' => 'cb',
        'maestro' => 'maestro',
        'bcmc' => 'bcmc'
    ];

    /**
     * @var string[]
     */
    protected $_cardPaymentMethod = [
        'hipay_hosted_fields',
        'hipay_hosted',
        'hipay_hostedmoto'
    ];

    /**
     * @var \HiPay\FullserviceMagento\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var CartFactory
     */
    protected $_cartFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
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
     * @var GroupRepositoryInterface
     */
    protected $_groupRepositoryInterface;

    /**
     *
     * @var \Magento\Framework\App\State $appState
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Url
     */
    protected $frontendUrlBuilder;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_httpHeader;

    /**
     * @var OrderExpirationTimes
     */
    protected $expirationSource;

    /**
     * @inheritDoc
     *
     * @param LoggerInterface $logger
     * @param Data $checkoutData
     * @param Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ResolverInterface $localeResolver
     * @param Factory $requestFactory
     * @param UrlInterface $urlBuilder
     * @param Url $frontendUrlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param CartFactory $cartFactory
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param CollectionFactory $mappingCategoriesCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param GroupRepositoryInterface $groupRepositoryInterface
     * @param State $appState
     * @param Header $httpHeader
     * @param OrderExpirationTimes $expirationSource
     * @param array $params
     * @throws LocalizedException
     */
    public function __construct(
        LoggerInterface $logger,
        Data $checkoutData,
        Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url $frontendUrlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Model\Cart\CartFactory $cartFactory,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        CollectionFactory $mappingCategoriesCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        GroupRepositoryInterface $groupRepositoryInterface,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\HTTP\Header $httpHeader,
        OrderExpirationTimes $expirationSource,
        array $params = []
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
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->appState = $appState;
        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_groupRepositoryInterface = $groupRepositoryInterface;
        $this->_httpHeader = $httpHeader;
        $this->expirationSource = $expirationSource;

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Order instance is required.'));
        }

        if (isset($params['operation'])) {
            $this->_operation = $params['operation'];
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
     * Get HiPay credit card type
     *
     * @param string|null $mageCcType
     * @return false|mixed|string
     */
    protected function getCcTypeHipay($mageCcType)
    {

        if (in_array($mageCcType, array_keys($this->_ccTypes))) {
            return $this->_ccTypes[$mageCcType];
        }

        return false;
    }

    /**
     * Check if requested ECI is MO/TO
     *
     * @return bool
     */
    protected function isMOTO()
    {
        $eci = $this->_order->getForcedEci() ?: $this->_order->getPayment()->getAdditionalInformation('eci');
        if ($eci == ECI::MOTO) {
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
     * Return payment product
     *
     * If Payment requires specified option ( With Fees or without Fees return it otherwhise normal payment product)
     *
     * @return string
     */
    private function getSpecifiedPaymentProduct()
    {
        return ($this->getPaymentProductFees()) ? $this->getPaymentProductFees() :
            $this->_order->getPayment()->getAdditionalInformation('payment_product') ??
            $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_products');
    }

    /**
     * Map order request data
     *
     * @return OrderRequest
     * @throws LocalizedException
     */
    public function mapRequest()
    {
        $payment_product = $this->getSpecifiedPaymentProduct();
        $useOrderCurrency = $this->_config->useOrderCurrency();

        $orderRequest = new OrderRequest();
        $orderRequest->orderid = $this->_order->getForcedOrderId() ?: $this->_order->getIncrementId();
        $orderRequest->operation = $this->_order->getForcedOperation() ?:
            $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_action');
        $orderRequest->payment_product = $payment_product &&
            !$this->_order->getPayment()->getMethodInstance()->getConfigData('is_multi_payment_products') ?
            $payment_product :
            $this->getCcTypeHipay($this->_order->getPayment()->getCcType());
        $orderRequest->description = $this->_order->getForcedDescription() ?: sprintf(
            "Order %s",
            $this->_order->getIncrementId()
        );
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
        $xForwardedFor = $this->_order->getXForwardedFor();
        $orderRequest->ipaddr = $xForwardedFor ? explode(',', $xForwardedFor)[0] : $this->_order->getRemoteIp();
        $orderRequest->language = $this->_localeResolver->getLocale();

        $redirectParams = ['_secure' => true];
        if ($this->isMOTO()) {
            $redirectParams['is_moto'] = true;
        }
        // url builder depend on context (admin or frontend)
        // if we send payment link to the customer
        // he/she must be redirect to frontend controller
        if ($this->_config->getValue('send_mail_to_customer')) {
            $this->_urlBuilder = $this->frontendUrlBuilder;
        }

        // URL callback
        $orderRequest->accept_url = $this->_urlBuilder->getUrl('hipay/redirect/accept', $redirectParams);
        $orderRequest->pending_url = $this->_urlBuilder->getUrl('hipay/redirect/pending', $redirectParams);
        $orderRequest->decline_url = $this->_urlBuilder->getUrl('hipay/redirect/decline', $redirectParams);
        $orderRequest->cancel_url = $this->_urlBuilder->getUrl('hipay/redirect/cancel', $redirectParams);
        $orderRequest->exception_url = $this->_urlBuilder->getUrl('hipay/redirect/exception', $redirectParams);

        if ($this->_config->isSendingNotifyUrl()) {
            $orderRequest->notify_url = $this->frontendUrlBuilder->getUrl("hipay/notify/index");
        }

        $orderRequest->paymentMethod = $this->_paymentMethod;

        $orderRequest->customerBillingInfo = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\Info\BillingInfo::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->customerShippingInfo = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        // Add 3DSv2 information if payment method is a credit card
        if (in_array($this->_order->getPayment()->getMethod(), $this->_cardPaymentMethod)) {
            $this->mapThreeDsInformation($orderRequest);
        }

        // Extras information
        $this->processExtraInformations($orderRequest, $useOrderCurrency);

        if ($payment_product == 'multibanco') {
            $validExpirationKeys = array_keys($this->expirationSource->getValues());
            $timeLimit = (string) $this->_config->getValue('multibanco_order_expiration_time');
            if ($timeLimit && in_array($timeLimit, $validExpirationKeys)) {
                $orderRequest->expiration_limit = $timeLimit;
            }
        }

        if ($payment_product == 'paypal') {
            $paypalOrderID = $this->_order->getPayment()->getAdditionalInformation('paypal_order_id');
            if ($paypalOrderID !== null) {
                $providerData = ['paypal_id' => $paypalOrderID];
                $orderRequest->provider_data = (string) json_encode($providerData);
            }
        }

        if (preg_match("/[34]xcb-no-fees|[34]xcb|credit-long/", $payment_product ?: '')) {
            $merchantPromotion = $this->_config->getValue('merchant_promotion');
            $orderRequest->payment_product_parameters = json_encode(
                [
                    "merchant_promotion" => $merchantPromotion && !empty($merchantPromotion) ?
                    $merchantPromotion :
                    \HiPay\Fullservice\Helper\MerchantPromotionCalculator::calculate(
                        $payment_product,
                        $orderRequest->amount
                    )
                ]
            );
        }

        $oneClick = $this->_order->getPayment()->getAdditionalInformation('create_oneclick') == '1';

        if ($oneClick && $this->_order->getPayment()->getMethod() === HostedFieldsMethod::HIPAY_METHOD_CODE) {
            $orderRequest->one_click = true;
        }

        $orderRequest->http_user_agent = $this->_httpHeader->getHttpUserAgent();

        return $orderRequest;
    }

    /**
     * Map 3DSv2 information , Use Classes from PHP SDK
     *
     * @param OrderRequest $orderRequest
     * @return void
     */
    protected function mapThreeDsInformation(OrderRequest &$orderRequest)
    {
        $orderRequest->account_info = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\ThreeDS\AccountInfoFormatter::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->previous_auth_info = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\ThreeDS\PreviousAuthInfoFormatter::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->merchant_risk_statement = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\ThreeDS\MerchantRiskStatementFormatter::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->browser_info = $this->_requestFactory->create(
            \HiPay\FullserviceMagento\Model\Request\ThreeDS\BrowserInfoFormatter::class,
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->device_channel = $this->getDeviceChannel();
    }

    /**
     * Get device channel
     *
     * @return int
     */
    public function getDeviceChannel()
    {
        return DeviceChannel::BROWSER;
    }

    /**
     * Process all extras information for the request
     *
     * @param  OrderRequest $orderRequest
     * @param  bool         $useOrderCurrency
     * @throws \Exception
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
            $orderRequest->delivery_information = $this->_requestFactory->create(
                \HiPay\FullserviceMagento\Model\Request\Info\DeliveryInfo::class,
                ['params' => ['order' => $this->_order, 'config' => $this->_config]]
            )->getRequestObject();
        }

        // Technical parameter to track wich magento version is used
        $orderRequest->source = $this->helper->getRequestSource();

        /*
         *  Custom Data
         *
         * You can use these parameters to submit custom values
         * you wish to show in HiPay back office transaction details,
         * receive back in the API response messages,
         * in the notifications or to activate specific FPS rules.
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
            $orderRequest->customerBillingInfo->phone = preg_replace(
                '/^\+?33/',
                '0',
                $orderRequest->customerBillingInfo->phone
            );

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
        $customData = [];
        return $customData;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order|mixed
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get customer repository interface
     *
     * @return CustomerRepositoryInterface|\Magento\Sales\Model\Order
     */
    public function getCustomerRepositoryInterface()
    {
        return $this->_customerRepositoryInterface;
    }

    /**
     * Get group repository interface
     *
     * @return GroupRepositoryInterface
     */
    public function getGroupRepositoryInterface()
    {
        return $this->_groupRepositoryInterface;
    }

    /**
     * Get customer session
     *
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Get checkout data helper
     *
     * @return Data
     */
    public function getCheckoutData()
    {
        return $this->_checkoutData;
    }

    /**
     * Get logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get payment method
     *
     * @return \HiPay\Fullservice\Request\AbstractRequest
     */
    public function getPaymentMethod()
    {
        return $this->_paymentMethod;
    }

    /**
     * Get credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_ccTypes;
    }

    /**
     * Get card payment methods
     *
     * @return array
     */
    public function getCardPaymentMethod()
    {
        return $this->_cardPaymentMethod;
    }
}
