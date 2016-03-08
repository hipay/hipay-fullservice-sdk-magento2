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

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;


/**
 * Class PaymentMethod
 * @package HiPay\FullserviceMagento\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedMethod extends FullserviceMethod {
	
	const HIPAY_METHOD_CODE               = 'hipay_hosted';
	
	/**
	 * @var string
	 */
	protected $_formBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Form';
	
	/**
	 * @var string
	 */
	protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Info';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_isInitializeNeeded = true;
	
	
	/**
	 * Instantiate state and set it to state object
	 *
	 * @param string $paymentAction
	 * @param \Magento\Framework\DataObject $stateObject
	 * @return void
	 */
	public function initialize($paymentAction, $stateObject)
	{

		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		$order->setCanSendNewEmailFlag(false);
		$payment->setAmountAuthorized($order->getTotalDue());
		$payment->setBaseAmountAuthorized($order->getBaseTotalDue());
		
		$this->_setHostedUrl($order);
		
		$stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
		$stateObject->setStatus('pending_payment');
		$stateObject->setIsNotified(false);

	}
	
	protected function _setHostedUrl(\Magento\Sales\Model\Order $order){

		//Create gateway manage with order data
		$gateway = $this->_gatewayManagerFactory->create($order);
			
		//Call fullservice api to get hosted page url
		$hppModel = $gateway->requestHostedPaymentPage();
		$order->getPayment()->setAdditionalInformation('redirectUrl',$hppModel->getForwardUrl());

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
	
			} 
	
	
		} catch (\Exception $e) {
			$this->_logger->critical($e);
			throw new LocalizedException(__('There was an error capturing the transaction: %1.', $e->getMessage()));
		}
	
	
		return $this;
	}
	
	

	
}