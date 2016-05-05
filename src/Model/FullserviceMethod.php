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
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use Magento\Payment\Model\InfoInterface;
use HiPay\Fullservice\Enum\Transaction\TransactionState;
use Magento\Framework\Exception\LocalizedException;


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
	protected $_isInitializeNeeded = false;
	
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
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;
	
	/**
	 * @var string[] keys to import in payment additionnal informations
	 */
	protected $_additionalInformationKeys = ['card_token','create_oneclick'];
	
	
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
			\Magento\Framework\Url $urlBuilder,
	        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []){
	
				parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger,$resource,$resourceCollection,$data);
				
				$this->_gatewayManagerFactory = $gatewayManagerFactory;
				$this->_debugReplacePrivateDataKeys = array('token','cardtoken','card_number','cvc');
				$this->urlBuilder = $urlBuilder;
	}
	
	/**
	 * Assign data to info model instance
	 *
	 * @param array|\Magento\Framework\DataObject $data
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 */
	public function assignData(\Magento\Framework\DataObject $data)
	{
		if (!$data instanceof \Magento\Framework\DataObject) {
			$data = new \Magento\Framework\DataObject($data);
		}

		$this->getInfoInstance()->addData($data->getData());
		
		$this->_assignAdditionalInformation($data);
		
		return $this;
	}
	
	protected function _assignAdditionalInformation(\Magento\Framework\DataObject $data){
		
		$info = $this->getInfoInstance();
		foreach ($this->_additionalInformationKeys as $key) {	
			if(!is_null($data->getData($key))){			
				$info->setAdditionalInformation($key,$data->getData($key));
			}
		}
		
		return $this;
	}
	
	/**
	 * Check method for processing with base currency
	 *
	 * @param string $currencyCode
	 * @return bool
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function canUseForCurrency($currencyCode)
	{
		if($this->getConfigData('allowed_currencies') != ""){
			return in_array($currencyCode,explode(",",$this->getConfigData('allowed_currencies')));
		}
		return true;
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
	 * Mapper from HiPay-specific payment actions to Magento payment actions
	 *
	 * @return string|null
	 */
	public function getConfigPaymentAction()
	{
		$action = $this->getConfigData('payment_action');
		switch ($action) {
			case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_AUTH:
				return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
			case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_SALE:
				return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
		}
		return $action;
	}
	
	public function place(\Magento\Payment\Model\InfoInterface $payment){
	
		try {
	
			$response = $this->_getGatewayManager($payment->getOrder())->requestNewOrder();
				
			$successUrl =  $this->urlBuilder->getUrl('checkout/onepage/success',['_secure'=>true]);
			$pendingUrl = $successUrl;
			$forwardUrl = $response->getForwardUrl();;
			$failUrl = $this->urlBuilder->getUrl('checkout/onepage/failure',['_secure'=>true]);
			$redirectUrl = $successUrl;
			switch($response->getState()){
				case TransactionState::COMPLETED:
					//redirectUrl is success by default
					break;
				case TransactionState::PENDING:
					$redirectUrl = $pendingUrl;
					break;
				case TransactionState::FORWARDING:
					$redirectUrl = $forwardUrl;
					break;
				case TransactionState::DECLINED:
					$redirectUrl = $failUrl;
					break;
				case TransactionState::ERROR:
					throw new LocalizedException(__('There was an error request new transaction: %1.', $response->getReason()));
				default:
					$redirectUrl = $failUrl;
			}
				
			//always in pending, because only notification can change order/transaction statues
			$payment->setIsTransactionPending(true);
				
			$payment->setAdditionalInformation('redirectUrl',$redirectUrl);
	
		} catch (\Exception $e) {
	
			$this->_logger->critical($e);
			throw new LocalizedException(__('There was an error request new transaction: %1.', $e->getMessage()));
		}
		return $this;
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
		try {
			/** @var \Magento\Sales\Model\Order\Payment $payment */
			if ($payment->getCcTransId()) {  //Is not the first transaction
				// As we alredy hav a transaction reference, we can request a capture operation.
				$this->_getGatewayManager($payment->getOrder())->requestOperationCapture($amount);
	
			} else { //Ok, it's the first transaction, so we request a new order
				$this->place($payment);
	
			}
	
		} catch (LocalizedException $e) {
			throw $e;
		}
		catch (\Exception $e) {
			$this->_logger->critical($e);
			throw new LocalizedException(__('There was an error capturing the transaction: %1.', $e->getMessage()));
		}
	
	
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
		$this->_getGatewayManager($payment->getOrder())->requestOperationRefund($amount);
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
		$this->_getGatewayManager($payment->getOrder())->requestOperationAcceptChallenge();
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
		$this->_getGatewayManager($payment->getOrder())->requestOperationDenyChallenge();
		return false;
	}
	
	/**
	 * 
	 * @param \Magento\Sales\Model\Order $order
	 * @return \HiPay\FullserviceMagento\Model\Gateway\Manager
	 */
	protected function _getGatewayManager($order){
		return $this->_gatewayManagerFactory->create($order);
	}
	
}
