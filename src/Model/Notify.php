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
						$this->_doTransactionAuthorization();
						break;
					case TransactionStatus::CAPTURE_REQUESTED:
						$this->_doTransactionCaptureRequested();
						break;
					case TransactionStatus::CAPTURED:
					case TransactionStatus::PARTIALLY_CAPTURED:
						$this->_doTransactionCapture();
						break;
					case TransactionStatus::REFUND_REQUESTED:
					case TransactionStatus::PARTIALLY_REFUNDED:
						$this->_doTransactionRefundRequested();
						break;
					case TransactionStatus::REFUNDED:
							$this->_doTransactionRefund();
						break;
					case TransactionStatus::REFUND_REFUSED:
						
						break;
				}
				break;
			case TransactionState::PENDING :
					$this->_doTransactionAuthorizedAndPending();
				break;
			case TransactionState::FORWARDING :
				break;
			case TransactionState::DECLINED :
				$this->_doTransactionDenied();
				break;
			default:
				$this->_doTransactionFailure();
		
				 
		}
		
		return $this;
	}
	
	/**
	 * Process a refund
	 *
	 * @return void
	 */
	protected function _doTransactionRefund()
	{
		
		$isCompleteRefund = true;
		$parentTransactionId = $this->_order->getPayment()->getLastTransId();
		
		$payment = $this->_order->getPayment()
								->setPreparedMessage($this->_generateComment(''))
								->setTransactionId($this->_transaction->getTransactionReference(). "-refund")
								->setParentTransactionId($parentTransactionId)
								->setIsTransactionClosed($isCompleteRefund)
								->registerRefundNotification(-1 * $this->_transaction->getRefundedAmount());
		$this->_order->save();

		$creditMemo = $payment->getCreatedCreditmemo();
		if ($creditMemo) {
			$this->creditmemoSender->send($creditMemo);
			$this->_order->addStatusHistoryComment(__('You notified customer about creditmemo #%1.', $creditMemo->getIncrementId()))
							->setIsCustomerNotified(true)
							->save();
		}
	}
	
	/**
	 * Process authorized and pending payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionAuthorizedAndPending()
	{
	
		$this->_order->getPayment()->setIsTransactionPending(true);
		$this->_doTransactionAuthorization();
	}

	

	/**
	 * Process capture requested payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionCaptureRequested()
	{
	
		$this->_generateComment('Capture Requested.',true);
		$this->_order->setStatus(Config::STATUS_CAPTURE_REQUESTED);
		$this->_order->save();
	}
	
	/**
	 * Process refund requested payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionRefundRequested()
	{
	
		$this->_generateComment('Refund Requested.',true);
		$this->_order->setStatus(Config::STATUS_REFUND_REQUESTED);
		$this->_order->save();
	}
	
	/**
	 * Process refund refused payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionRefundRefused()
	{
	
		$this->_generateComment('Refund Refused.',true);
		$this->_order->save();
	}
	
	
	/**
	 * Process denied payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionDenied()
	{
	
		$this->_order->getPayment()
						->setTransactionId($this->_transaction->getTransactionReference(). "-denied")
						->setNotificationResult(true)
						->setIsTransactionClosed(true)
						->deny(false);
		
		$this->_order->save();
	}
	
	/**
	 * Treat failed payment as order cancellation
	 *
	 * @return void
	 */
	protected function _doTransactionFailure()
	{
		$this->_order->registerCancellation($this->_generateComment(''))->save();
	}
	
	
	/**
	 * Register authorized payment
	 * @return void
	 */
	protected function _doTransactionAuthorization()
	{
		/** @var $payment \Magento\Sales\Model\Order\Payment */
		$payment = $this->_order->getPayment();
	
		$payment->setPreparedMessage($this->_generateComment(''))
				->setTransactionId($this->_transaction->getTransactionReference() . "-authorization")
				/*->setParentTransactionId(null)*/
				->setCurrencyCode($this->_transaction->getCurrency())
				->setIsTransactionClosed(0)
				->registerAuthorizationNotification((float)$this->_transaction->getAuthorizedAmount());
												 
		if (!$this->_order->getEmailSent()) {
			$this->orderSender->send($this->_order);
		}
		
		//Set custom status
		$this->_order->setStatus(Config::STATUS_AUTHORIZED);
		
		$this->_order->save();
	}
	
	/**
	 * Process completed payment (either full or partial)
	 *
	 * @param bool $skipFraudDetection
	 * @return void
	 */
	protected function _doTransactionCapture($skipFraudDetection = false)
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