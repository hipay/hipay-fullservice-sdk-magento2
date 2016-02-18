<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use \HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use Magento\Payment\Model\InfoInterface;

/**
 *
 * @author kassim
 *        
 */
abstract class FullserviceMethod extends AbstractMethod {
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_isGateway = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canAuthorize = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canCapture = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canCapturePartial = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canCaptureOnce = false;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefund = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefundInvoicePartial = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_isInitializeNeeded = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canReviewPayment = true;

	
	/**
	 * Fields that should be replaced in debug with '***'
	 *
	 * @var array
	 */
	protected $_debugReplacePrivateDataKeys = [];
	
	/**
	 * 
	 * @var ManagerFactory $_gatewayManagerFactory
	 */
	protected $_gatewayManagerFactory;
	
	
	
	/**
	 *
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
	 * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
	 * @param \Magento\Payment\Helper\Data $paymentData
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Payment\Model\Method\Logger $logger
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
	        \Magento\Framework\Registry $registry,
	        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
	        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
	        \Magento\Payment\Helper\Data $paymentData,
	        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	        \Magento\Payment\Model\Method\Logger $logger,
			ManagerFactory $gatewayManagerFactory,
	        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []){
	
				parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger,$resource,$resourceCollection,$data);
				
				$this->_gatewayManagerFactory = $gatewayManagerFactory;
				$this->_debugReplacePrivateDataKeys = array('token','cardtoken','card_number','cvc');
	}
	
	/**
	 * Check whether payment method can be used with the quote
	 *
	 * @param \Magento\Quote\Api\Data\CartInterface|null $quote
	 * @return bool
	 */
	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
	{
		return $this->isActive($quote ? $quote->getStoreId() : null);
	}

	/**
	 * Capture payment method
	 *
	 * @param \Magento\Framework\DataObject|InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		parent::capture($payment, $amount);
		$this->_getManager($payment->getOrder())->requestOperationCapture($amount);
		return $this;
	}
	
	/**
	 * Refund specified amount for payment
	 *
	 * @param \Magento\Framework\DataObject|InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount){
		parent::refund($payment, $amount);
		$this->_getManager($payment->getOrder())->requestOperationRefund($amount);
		return $this;
	}
	
	/**
	 * Attempt to accept a payment that us under review
	 *
	 * @param InfoInterface $payment
	 * @return false
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function acceptPayment(InfoInterface $payment){
		parent::acceptPayment($payment);
		$this->_getManager($payment->getOrder())->requestOperationAcceptChallenge();
		return false;
	}
	
	
	/**
	 * Attempt to deny a payment that us under review
	 *
	 * @param InfoInterface $payment
	 * @return false
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function denyPayment(InfoInterface $payment){
		parent::denyPayment($payment);
		$this->_getManager($payment->getOrder())->requestOperationDenyChallenge();
		return false;
	}
	
	/**
	 * 
	 * @param \Magento\Sales\Model\Order $order
	 * @return \HiPay\FullserviceMagento\Model\GatewayManager
	 */
	protected function _getManager($order){
		return $this->_gatewayManagerFactory->create($order);
	}
	
}
