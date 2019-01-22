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
namespace HiPay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;

/**
 * Class Context for payments methods
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{

    /**
     *
     * @var ManagerFactory $_gatewayManagerFactory
     */
    protected $_gatewayManagerFactory;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    public $urlBuilder;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Email\Sender\FraudAcceptSender $fraudAcceptSender
     */
    protected $fraudAcceptSender;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender $fraudDenySender
     */
    protected $fraudDenySender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Card  model Factory
     *
     * @var \HiPay\FullserviceMagento\Model\CardFactory
     */
    protected $_cardFactory;

    /**
     * Config factory
     * @var \HiPay\FullserviceMagento\Model\Config\Factory $configFactor
     */
    protected $_configFactory;

    /**
     *
     * @var \Magento\Framework\Model\Context $_context
     */
    protected $_modelContext;

    /**
     *
     * @var \Magento\Framework\Registry $_registry
     */
    protected $_registry;

    /**
     *
     * @var \Magento\Framework\Api\ExtensionAttributesFactory $_extensionFactory
     */
    protected $_extensionFactory;

    /**
     *
     * @var \Magento\Framework\Api\AttributeValueFactory $customAttributeFactor
     */
    protected $_customAttributeFactor;

    /**
     *
     * @var \Magento\Payment\Helper\Data $paymentData
     */
    public $_paymentData;

    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $_scopeConfig;

    /**
     *
     * @var \Magento\Payment\Model\Method\Logger $logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Context constructor.
     * @param \Magento\Framework\Model\Context $modelContext
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param ManagerFactory $gatewayManagerFactory
     * @param \Magento\Framework\Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender $fraudDenySender
     * @param \HiPay\FullserviceMagento\Model\Email\Sender\FraudAcceptSender $fraudAcceptSender
     * @param \HiPay\FullserviceMagento\Model\Config\Factory $configFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \HiPay\FullserviceMagento\Model\CardFactory $cardFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $modelContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        ManagerFactory $gatewayManagerFactory,
        \Magento\Framework\Url $urlBuilder,
        \HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender $fraudDenySender,
        \HiPay\FullserviceMagento\Model\Email\Sender\FraudAcceptSender $fraudAcceptSender,
        \HiPay\FullserviceMagento\Model\Config\Factory $configFactory,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {

        //Abstract Method objects
        $this->_modelContext = $modelContext;
        $this->_registry = $registry;
        $this->_extensionFactory = $extensionFactory;
        $this->_customAttributeFactor = $customAttributeFactory;
        $this->_paymentData = $paymentData;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;

        //Custom object
        $this->_gatewayManagerFactory = $gatewayManagerFactory;
        $this->urlBuilder = $urlBuilder;
        $this->fraudAcceptSender = $fraudAcceptSender;
        $this->fraudDenySender = $fraudDenySender;
        $this->_configFactory = $configFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_cardFactory = $cardFactory;
        $this->priceCurrency = $priceCurrency;
    }

    public function getGatewayManagerFactory()
    {
        return $this->_gatewayManagerFactory;
    }

    public function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    public function getFraudAcceptSender()
    {
        return $this->fraudAcceptSender;
    }

    public function getFraudDenySender()
    {
        return $this->fraudDenySender;
    }

    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    public function getCardFactory()
    {
        return $this->_cardFactory;
    }

    public function getConfigFactory()
    {
        return $this->_configFactory;
    }

    public function getModelContext()
    {
        return $this->_modelContext;
    }

    public function getRegistry()
    {
        return $this->_registry;
    }

    public function getExtensionFactory()
    {
        return $this->_extensionFactory;
    }

    public function getCustomAttributeFactor()
    {
        return $this->_customAttributeFactor;
    }

    public function getPaymentData()
    {
        return $this->_paymentData;
    }

    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function getPriceCurrency()
    {
        return $this->priceCurrency;
    }
}
