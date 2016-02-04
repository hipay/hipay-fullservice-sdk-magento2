<?php
/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace Hipay\FullserviceMagento\Model;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Hipay\Fullservice\Gateway\Model\Transaction;
use Hipay\Fullservice\Gateway\Mapper\TransactionMapper;
use Hipay\Fullservice\Enum\Transaction\TransactionState;
use Hipay\Fullservice\Enum\Transaction\TransactionStatus;

class Notify {
	
	/**
	 *
	 * @var  \Psr\Log\LoggerInterface $_logger
	 */
	protected $_logger;
	
	/**
	 *
	 * @var \Magento\Sales\Model\OrderFactory $_orderFactory
	 */
	protected $_orderFactory;
	
	/**
	 * @var OrderSender
	 */
	protected $orderSender;
	
	/**
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	/**
	 * 
	 * @var Transaction $_transaction
	 */
	protected $_transaction;
	
	
	public function __construct(
			\Psr\Log\LoggerInterface $_logger,
			\Magento\Sales\Model\OrderFactory $orderFactory,
			OrderSender $orderSender,
			$params = []
			){
		
			$this->_logger = $_logger;
			$this->_orderFactory = $orderFactory;
			$this->orderSender = $orderSender;
	
			if (isset($params['response']) && is_array($params['response'])) {
				$this->_transaction = (new TransactionMapper($params['response']))->getModelObjectMapped();
				
				$this->_order = $this->_orderFactory->create()->loadByIncrementId($this->_transaction->getOrder()->getId());
				if (!$this->_order->getId()) {
					throw new \Exception(sprintf('Wrong order ID: "%s".', $this->_transaction->getOrder()->getId()));
				}
				
			} else {
				throw new \Exception('Posted data response as array is required.');
			}
		
	}
	
	
	
	
	public function processTransaction(){

		switch ($this->_transaction->getState()){
			case TransactionState::COMPLETED :
				switch ($this->_transaction->getStatus()){
					case TransactionStatus::AUTHORIZED:
		
						$this->_doPaymentAuthorization();
		
						break;
					case TransactionStatus::CAPTURE_REQUESTED:
						$this->_generateComment('Capture Requested.',true);
						$this->_order->save();
						break;
					case TransactionStatus::CAPTURED:
						$this->_doPaymentCapture();
						break;
				}
				break;
			case TransactionState::PENDING :
				$this->_order->getPayment()->setIsTransactionPending(true);
				$this->_doPaymentAuthorization();
				break;
			case TransactionState::FORWARDING :
				break;
			case TransactionState::DECLINED :
				$this->_doPaymentDenied();
				break;
			default:
				$this->_doPaymentFailure();
		
				 
		}
		
		return $this;
	}
	
	/**
	 * Process denied payment notification
	 *
	 * @return void
	 */
	protected function _doPaymentDenied()
	{
	
		$this->_order->getPayment()->setTransactionId(
				$this->_transaction->getTransactionReference(). "-denied"
				)->setNotificationResult(
						true
						)->setIsTransactionClosed(
								true
								)->deny(false);
								$this->_order->save();
	}
	
	/**
	 * Treat failed payment as order cancellation
	 *
	 * @return void
	 */
	protected function _doPaymentFailure()
	{
		$this->_order->registerCancellation($this->_generateComment(''))->save();
	}
	
	
	/**
	 * Register authorized payment
	 * @return void
	 */
	protected function _doPaymentAuthorization()
	{
		/** @var $payment \Magento\Sales\Model\Order\Payment */
		$payment = $this->_order->getPayment();
	
		$payment->setPreparedMessage(
				$this->_generateComment('')
				)->setTransactionId(
						$this->_transaction->getTransactionReference() . "-authorization"
						)/*->setParentTransactionId(
								null
								)*/->setCurrencyCode(
										$this->_transaction->getCurrency()
										)->setIsTransactionClosed(
												0
												)->registerAuthorizationNotification(
														(float)$this->_transaction->getAuthorizedAmount()
														);
												 
												if (!$this->_order->getEmailSent()) {
													$this->orderSender->send($this->_order);
												}
												$this->_order->save();
	}
	
	/**
	 * Process completed payment (either full or partial)
	 *
	 * @param bool $skipFraudDetection
	 * @return void
	 */
	protected function _doPaymentCapture($skipFraudDetection = false)
	{
		 
		/* @var $payment \Magento\Sales\Model\Order\Payment */
		 
		$payment = $this->_order->getPayment();
		 
		$parentTransactionId = $payment->getLastTransId();
		 
		$payment->setTransactionId(
				$this->_transaction->getTransactionReference() . "-capture"
				);
		$payment->setCurrencyCode(
				$this->_transaction->getCurrency()
				);
		$payment->setPreparedMessage(
				$this->_generateComment('')
				);
		$payment->setParentTransactionId(
				$parentTransactionId
				);
		$payment->setShouldCloseParentTransaction(
				true
				);
		$payment->setIsTransactionClosed(
				0
				);
		$payment->registerCaptureNotification(
				$this->_transaction->getCapturedAmount(),
				$skipFraudDetection && $parentTransactionId
				);
		$this->_order->save();
	
		// notify customer
		$invoice = $payment->getCreatedInvoice();
		if ($invoice && !$this->_order->getEmailSent()) {
			$this->orderSender->send($this->_order);
			$this->_order->addStatusHistoryComment(
					__('You notified customer about invoice #%1.', $invoice->getIncrementId())
					)->setIsCustomerNotified(
							true
							)->save();
		}
	}
	
	/**
	 * Generate an "Notification" comment with additional explanation.
	 * Returns the generated comment or order status history object
	 * @param string $comment
	 * @param bool $addToHistory
	 * @return string|\Magento\Sales\Model\Order\Status\History
	 */
	protected function _generateComment($comment = '', $addToHistory = false)
	{
		$message = __('Notification "%1"', $this->_transaction->getState());
		if ($comment) {
			$message .= ' ' . $comment;
		}
		if ($addToHistory) {
			$message = $this->_order->addStatusHistoryComment($message);
			$message->setIsCustomerNotified(null);
		}
		return $message;
	}
	
}