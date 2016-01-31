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
namespace Hipay\FullserviceMagento\Controller\Notify;

use Magento\Framework\App\Action\Action as AppAction;
use Hipay\Fullservice\Enum\Transaction\TransactionState;
use Hipay\Fullservice\Enum\Transaction\TransactionStatus;

use Magento\Framework\App\Action\Context;
use Hipay\Fullservice\Gateway\Mapper\TransactionMapper;
use Hipay\Fullservice\Helper\Convert;
use Hipay\Fullservice\Gateway\Model\Transaction;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Index extends AppAction {
	
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
	
	protected $orderSender;
	
	/**
	 * @param Context $context
	 */
	public function __construct(
			Context $context,
			\Psr\Log\LoggerInterface $_logger,
			\Magento\Sales\Model\OrderFactory $orderFactory,
			OrderSender $orderSender
			){
		parent::__construct($context);
		
		$this->_logger = $_logger;
		$this->_orderFactory = $orderFactory;
		$this->orderSender = $orderSender;
		
	}
	
	/**
	 * 
	 * @var \Magento\Framework\App\Request\Http $_request
	 */
	protected $_request;
	
	/**
	 * 
	 * @var \Magento\Sales\Model\Order $_order
	 */
	protected $_order;
	/**
	 * 
	 * @var \Hipay\Fullservice\Gateway\Model\Transaction $_transaction
	 */
	protected $_transaction;
	
	protected function _validateSignature()
	{
	    $signature= $this->_request->getServerValue('HTTP_X_ALLOPASS_SIGNATURE');
		//@TODO check signature passphrase
		
		return true;
	}
	
	/**
	 * @return void
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * */
     public function execute(){
     	ini_set('display_errors',1);
     	error_reporting(E_ALL);
     	$params = $this->getRequest()->getParams();
     	
     	
     	$this->_transaction = (new TransactionMapper($params))->getModelObjectMapped();
 
     	$this->_order = $this->_orderFactory->create()->loadByIncrementId($this->_transaction->getOrder()->getId());
     	if (!$this->_order->getId()) {
     		throw new \Exception(sprintf('Wrong order ID: "%s".', $this->_transaction->getOrder()->getId()));
     	}
     	
     	
     	switch ($this->_transaction->getState()){
     		case TransactionState::COMPLETED :
     			switch ($this->_transaction->getStatus()){
     				case TransactionStatus::AUTHORIZED:
     					
     					$this->_registerPaymentAuthorization();
     					
     					break;
     				case TransactionStatus::CAPTURE_REQUESTED:
     					$this->_createNotifyComment('Capture Requested.',true);
     					$this->_order->save();
     					break;
     				case TransactionStatus::CAPTURED:
     					$this->_registerPaymentCapture();
     					break;
     			}
     			break;
     		case TransactionState::PENDING :
     			$this->_createNotifyComment('Trasaction Is in Pending.',true);
     			$this->_order->getPayment()->setIsTransactionPending(true);
     			$this->_order->save();
     			break;
     		case TransactionState::FORWARDING :
     			break;
     		case TransactionState::DECLINED :
     			$this->_registerPaymentDenied();
     		break;
     		default:
     			$this->_registerPaymentFailure();
     			
     		
     	}

     	
     	//echo '<pre>';
     	//die(print_r($this->_transaction,true));
 		die('OK');
	 }
	 
	 /**
	  * Process denied payment notification
	  *
	  * @return void
	  */
	 protected function _registerPaymentDenied()
	 {

	 	$this->_order->getPayment()->setTransactionId(
	 			$this->_transaction->getTransactionReference()
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
	 protected function _registerPaymentFailure()
	 {
	 	$this->_order->registerCancellation($this->_createNotifyComment(''))->save();
	 }
	 
	 
	 /**
	  * Register authorized payment
	  * @return void
	  */
	 protected function _registerPaymentAuthorization()
	 {
	 	/** @var $payment \Magento\Sales\Model\Order\Payment */
	 	$payment = $this->_order->getPayment();
	
 		$payment->setPreparedMessage(
 				$this->_createNotifyComment('')
 				)->setTransactionId(
 						$this->_transaction->getTransactionReference()
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
	 protected function _registerPaymentCapture($skipFraudDetection = false)
	 {
	 	
	 	//$parentTransactionId = $this->getRequestData('parent_txn_id');
	 	
	 	$payment = $this->_order->getPayment();
	 	$payment->setTransactionId(
	 			$this->_transaction->getTransactionReference()
	 			);
	 	$payment->setCurrencyCode(
	 			$this->_transaction->getCurrency()
	 			);
	 	$payment->setPreparedMessage(
	 			$this->_createNotifyComment('')
	 			);
	 	/*$payment->setParentTransactionId(
	 			$parentTransactionId
	 			);*/
	 	/*$payment->setShouldCloseParentTransaction(
	 			'Completed' === this->_transaction->getState()
	 			);*/
	 	$payment->setIsTransactionClosed(
	 			0
	 			);
	 	$payment->registerCaptureNotification(
	 			$this->_transaction->getCapturedAmount(),
	 			$skipFraudDetection && false/*$parentTransactionId*/
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
	 protected function _createNotifyComment($comment = '', $addToHistory = false)
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