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

use HiPay\FullserviceMagento\Model\Request\CommonRequest as CommonRequest;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;

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
        $params = []
    )
    {

        parent::__construct($logger, $checkoutData, $customerSession, $checkoutSession, $localeResolver, $requestFactory,
            $urlBuilder, $helper, $cartFactory,$weeeHelper,$productRepositoryInterface,$mappingCategoriesCollectionFactory,$categoryFactory,$params);

        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;

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
    private function getPaymentProductFees() {
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
    private function getSpecifiedPaymentProduct(){
        return ($this->getPaymentProductFees()) ? $this->getPaymentProductFees():
                $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_products');;
    }


    /**
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    protected function mapRequest()
    {
        $payment_product = $this->getSpecifiedPaymentProduct();
        $orderRequest = new OrderRequest();
        $orderRequest->orderid = $this->_order->getForcedOrderId() ?: $this->_order->getIncrementId();
        $orderRequest->operation = $this->_order->getForcedOperation() ?: $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_action');
        $orderRequest->payment_product = $this->getCcTypeHipay($this->_order->getPayment()->getCcType()) ?: $payment_product ;
        $orderRequest->description = $this->_order->getForcedDescription() ?: sprintf("Order %s", $this->_order->getIncrementId()); //@TODO
        $orderRequest->long_description = "";
        $orderRequest->currency = $this->_order->getBaseCurrencyCode();
        $orderRequest->amount = $this->_order->getForcedAmount() ?: (float)$this->_order->getBaseGrandTotal();
        $orderRequest->shipping = (float)$this->_order->getShippingAmount();
        $orderRequest->tax = (float)$this->_order->getTaxAmount();
        $orderRequest->cid = $this->_customerId;
        $orderRequest->ipaddr = $this->_order->getRemoteIp();

        $redirectParams = ['_secure' => true];

        if ($this->isMOTO()) {
            $redirectParams['is_moto'] = true;
        }

        $orderRequest->accept_url = $this->_urlBuilder->getUrl('hipay/redirect/accept', $redirectParams);
        $orderRequest->pending_url = $this->_urlBuilder->getUrl('hipay/redirect/pending', $redirectParams);
        $orderRequest->decline_url = $this->_urlBuilder->getUrl('hipay/redirect/decline', $redirectParams);
        $orderRequest->cancel_url = $this->_urlBuilder->getUrl('hipay/redirect/cancel', $redirectParams);
        $orderRequest->exception_url = $this->_urlBuilder->getUrl('hipay/redirect/exception', $redirectParams);

        // Check if fingerprint is enabled
        if ($this->_config->isFingerprintEnabled()) {
            $orderRequest->device_fingerprint = $this->_order->getPayment()->getAdditionalInformation('fingerprint');;
        }

        // Check if sending cart is necessary ( If  conf enabled or if payment method product needs it )
        if ($this->_config->isNecessaryToSendCartItems($orderRequest->payment_product)) {
            $orderRequest->basket = $this->processCartFromOrder($this->_operation);
        }

        // Check if delivery method is required for the payment method
        if ($this->_config->isDeliveryMethodRequired($orderRequest->payment_product)){
            $orderRequest->delivery_information = $this->_requestFactory->create('\HiPay\FullserviceMagento\Model\Request\Info\DeliveryInfo', ['params' => ['order' => $this->_order, 'config' => $this->_config]])->getRequestObject();
        }

        $orderRequest->language = $this->_localeResolver->getLocale();
        $orderRequest->paymentMethod = $this->_paymentMethod;
        $orderRequest->customerBillingInfo = $this->_requestFactory->create('\HiPay\FullserviceMagento\Model\Request\Info\BillingInfo', ['params' => ['order' => $this->_order, 'config' => $this->_config]])->getRequestObject();
        $orderRequest->customerShippingInfo = $this->_requestFactory->create('\HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo', ['params' => ['order' => $this->_order, 'config' => $this->_config]])->getRequestObject();

        // Technical parameter to track wich magento version is used
        $orderRequest->source = $this->helper->getRequestSource();

        return $orderRequest;

    }

}
