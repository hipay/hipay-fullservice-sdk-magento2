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

namespace HiPay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\CardFactory;
use HiPay\FullserviceMagento\Model\Config\Factory;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudAcceptSender;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender;
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Context for payments methods
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     *
     * @var ManagerFactory $_gatewayManagerFactory
     */
    protected $_gatewayManagerFactory;

    /**
     * @var \Magento\Framework\Url
     */
    public $urlBuilder;

    /**
     *
     * @var FraudAcceptSender $fraudAcceptSender
     */
    protected $fraudAcceptSender;

    /**
     *
     * @var FraudDenySender $fraudDenySender
     */
    protected $fraudDenySender;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * Card  model Factory
     *
     * @var CardFactory
     */
    protected $_cardFactory;

    /**
     * @var CollectionFactory
     */
    private $_cardCollectionFactory;

    /**
     * @var Factory $configFactor
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
     * @var ExtensionAttributesFactory $_extensionFactory
     */
    protected $_extensionFactory;

    /**
     *
     * @var AttributeValueFactory $customAttributeFactor
     */
    protected $_customAttributeFactor;

    /**
     *
     * @var \Magento\Payment\Helper\Data $paymentData
     */
    public $_paymentData;

    /**
     *
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $_scopeConfig;

    /**
     *
     * @var Logger $logger
     */
    protected $_logger;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Context constructor.
     *
     * @param \Magento\Framework\Model\Context $modelContext
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ManagerFactory $gatewayManagerFactory
     * @param \Magento\Framework\Url $urlBuilder
     * @param FraudDenySender $fraudDenySender
     * @param FraudAcceptSender $fraudAcceptSender
     * @param Factory $configFactory
     * @param Session $checkoutSession
     * @param CardFactory $cardFactory
     * @param CollectionFactory $cardCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $modelContext,
        \Magento\Framework\Registry      $registry,
        ExtensionAttributesFactory       $extensionFactory,
        AttributeValueFactory            $customAttributeFactory,
        \Magento\Payment\Helper\Data     $paymentData,
        ScopeConfigInterface             $scopeConfig,
        Logger                           $logger,
        ManagerFactory                   $gatewayManagerFactory,
        \Magento\Framework\Url           $urlBuilder,
        FraudDenySender                  $fraudDenySender,
        FraudAcceptSender                $fraudAcceptSender,
        Factory                          $configFactory,
        Session                          $checkoutSession,
        CardFactory                      $cardFactory,
        CollectionFactory                $cardCollectionFactory,
        PriceCurrencyInterface           $priceCurrency,
        StoreManagerInterface            $storeManager
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
        $this->_cardCollectionFactory = $cardCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get the gateway manager factory
     *
     * @return ManagerFactory
     */
    public function getGatewayManagerFactory()
    {
        return $this->_gatewayManagerFactory;
    }

    /**
     * Get the URL builder
     *
     * @return Url
     */
    public function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    /**
     * Get the sender used to accept fraud
     *
     * @return FraudAcceptSender
     */
    public function getFraudAcceptSender()
    {
        return $this->fraudAcceptSender;
    }

    /**
     * Get the sender used to deny fraud
     *
     * @return FraudDenySender
     */
    public function getFraudDenySender()
    {
        return $this->fraudDenySender;
    }

    /**
     * Get the current checkout session
     *
     * @return Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get the card model factory
     *
     * @return CardFactory
     */
    public function getCardFactory()
    {
        return $this->_cardFactory;
    }

    /**
     * Get the card collection factory
     *
     * @return CollectionFactory
     */
    public function getCardCollectionFactory()
    {
        return $this->_cardCollectionFactory;
    }

    /**
     * Get the config factory
     *
     * @return Factory
     */
    public function getConfigFactory()
    {
        return $this->_configFactory;
    }

    /**
     * Get the model context
     *
     * @return \Magento\Framework\Model\Context
     */
    public function getModelContext()
    {
        return $this->_modelContext;
    }

    /**
     * Get the registry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Get the extension attributes factory
     *
     * @return ExtensionAttributesFactory
     */
    public function getExtensionFactory()
    {
        return $this->_extensionFactory;
    }

    /**
     * Get the custom attribute factory
     *
     * @return AttributeValueFactory
     */
    public function getCustomAttributeFactor()
    {
        return $this->_customAttributeFactor;
    }

    /**
     * Get the payment data helper
     *
     * @return Data
     */
    public function getPaymentData()
    {
        return $this->_paymentData;
    }

    /**
     * Get the scope config interface
     *
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get the logger instance
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get the price currency
     *
     * @return PriceCurrencyInterface
     */
    public function getPriceCurrency()
    {
        return $this->priceCurrency;
    }

    /**
     * Get the store manager interface.
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
