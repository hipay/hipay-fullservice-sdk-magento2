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
use HiPay\FullserviceMagento\Model\Request\CommonRequest as CommonRequest;
use HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory;
use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * Order Request Object
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
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
        'MI' => 'maestro',
        'visa' => 'visa',
        'american-express' => 'american-express',
        'mastercard' => 'mastercard',
        'cb' => 'cb',
        'maestro' => 'maestro',
        'bcmc' => 'bcmc'
    );

    protected $_cardPaymentMethod = array(
        'hipay_hosted_fields',
        'hipay_hosted',
        'hipay_hostedmoto'
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
     * @var \Magento\Customer\Api\GroupRepositoryInterface
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
     * {@inheritDoc}
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
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
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepositoryInterface,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\HTTP\Header $httpHeader,
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
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->appState = $appState;
        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_groupRepositoryInterface = $groupRepositoryInterface;
        $this->_httpHeader = $httpHeader;

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Order instance is required.'));
        }

        if (isset($params['operation'])) {
            $this->_operation = $params['operation'];
        }

        if (
            isset($params['paymentMethod'])
            && $params['paymentMethod'] instanceof \HiPay\Fullservice\Request\AbstractRequest
        ) {
            $this->_paymentMethod = $params['paymentMethod'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Object Request PaymentMethod instance is required.')
            );
        }
    }

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
     *  Return payment product
     *
     *  If Payment requires specified option ( With Fees or without Fees return it otherwhise normal payment product)
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
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
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
        $orderRequest->ipaddr = $this->_order->getRemoteIp();
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
            '\HiPay\FullserviceMagento\Model\Request\Info\BillingInfo',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->customerShippingInfo = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        // Add 3DSv2 information if payment method is a credit card
        if (in_array($this->_order->getPayment()->getMethod(), $this->_cardPaymentMethod)) {
            $this->mapThreeDsInformation($orderRequest);
        }

        // Extras information
        $this->processExtraInformations($orderRequest, $useOrderCurrency);

        if ($payment_product == 'multibanco') {
            $timeLimit = $this->_config->getValue('multibanco_order_expiration_time');
            if ($timeLimit && in_array($timeLimit, [3, 30, 90])) {
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
                array(
                    "merchant_promotion" => $merchantPromotion && !empty($merchantPromotion) ?
                    $merchantPromotion :
                    \HiPay\Fullservice\Helper\MerchantPromotionCalculator::calculate(
                        $payment_product,
                        $orderRequest->amount
                    )
                )
            );
        }

        $orderRequest->http_user_agent = $this->_httpHeader->getHttpUserAgent();

        return $orderRequest;
    }

    /**
     * Map 3DSv2 information
     * Use Classes from PHP SDK
     *
     * @param $orderRequest
     */
    protected function mapThreeDsInformation(&$orderRequest)
    {
        $orderRequest->account_info = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\ThreeDS\AccountInfoFormatter',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->previous_auth_info = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\ThreeDS\PreviousAuthInfoFormatter',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->merchant_risk_statement = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\ThreeDS\MerchantRiskStatementFormatter',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->browser_info = $this->_requestFactory->create(
            '\HiPay\FullserviceMagento\Model\Request\ThreeDS\BrowserInfoFormatter',
            ['params' => ['order' => $this->_order, 'config' => $this->_config]]
        )->getRequestObject();

        $orderRequest->device_channel = $this->getDeviceChannel();
    }

    /**
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
                '\HiPay\FullserviceMagento\Model\Request\Info\DeliveryInfo',
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

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @return \Magento\Checkout\Helper\Data
     */
    public function getCheckoutData()
    {
        return $this->_checkoutData;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * @return \HiPay\Fullservice\Request\AbstractRequest
     */
    public function getPaymentMethod()
    {
        return $this->_paymentMethod;
    }

    /**
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_ccTypes;
    }

    /**
     * @return array
     */
    public function getCardPaymentMethod()
    {
        return $this->_cardPaymentMethod;
    }
}