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


use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\Fullservice\Gateway\Mapper\TransactionMapper;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudReviewSender;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use HiPay\Fullservice\Enum\Transaction\TransactionState;

class Notify {
	
	/**
	 *
	 * @var \Magento\Sales\Model\OrderFactory $_orderFactory
	 */
	protected $_orderFactory;
	
	/**
	 * @var FraudReviewSender
	 */
	protected $fraudReviewSender;
	
	/**
	 * @var FraudDenySender
	 */
	protected $fraudDenySender;
	
	/**
	 * 
	 * @var OrderSender $orderSender
	 */
	protected $orderSender;
	
	/**
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	/**
	 * 
	 * @var Transaction $_transaction Response Model Transaction
	 */
	protected $_transaction;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\FullserviceMethod $_methodInstance
	 */
	protected $_methodInstance;
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\CardFactory $_cardFactory
	 */
	protected $_cardFactory;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory
	 */
	protected $ppFactory;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory
	 */
	protected $spFactory;
	
	/**
	 * 
	 * @var bool $isSplitPayment
	 */
	protected $isSplitPayment = false;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\SplitPayment $splitPayment
	 */
	protected $splitPayment;
	
	/**
	 * @var ResourceOrder $orderResource
	 */
	protected $orderResource;

	
	public function __construct(
			\Magento\Sales\Model\OrderFactory $orderFactory,
			\HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
			OrderSender $orderSender,
			FraudReviewSender $fraudReviewSender,
			FraudDenySender $fraudDenySender,
			\Magento\Payment\Helper\Data $paymentHelper,
			\HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory,
			\HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory,
			ResourceOrder $orderResource,
			$params = []
			){

			$this->_orderFactory = $orderFactory;
			$this->_cardFactory = $cardFactory;
			$this->orderSender = $orderSender;
			$this->fraudReviewSender = $fraudReviewSender;
			$this->fraudDenySender = $fraudDenySender;

			$this->ppFactory = $ppFactory;
			$this->spFactory = $spFactory;

			$this->orderResource = $orderResource;

	
			if (isset($params['response']) && is_array($params['response'])) {
				
				$incrementId = $params['response']['order']['id'];
				if(strpos($incrementId,'-split-') !== false){
					list($realIncrementId,,$splitPaymentId) = explode("-",$incrementId);
					$params['response']['order']['id']= $realIncrementId;
					$this->isSplitPayment = true;
					$this->splitPayment = $this->spFactory->create()->load((int)$splitPaymentId);
					
					if(!$this->splitPayment->getId()){
						throw new \Exception(sprintf('Wrong Split Payment ID: "%s".', $splitPaymentId));
					}
					
				}
				
				$this->_transaction = (new TransactionMapper($params['response']))->getModelObjectMapped();

				$this->_order = $this->_orderFactory->create()->loadByIncrementId($this->_transaction->getOrder()->getId());
				
				if (!$this->_order->getId()) {
					throw new \Exception(sprintf('Wrong order ID: "%s".', $this->_transaction->getOrder()->getId()));
				}
								
				//Retieve method model
				$this->_methodInstance = $paymentHelper->getMethodInstance($this->_order->getPayment()->getMethod());
				
				//Debug transaction notification if debug enabled
				$this->_methodInstance->debugData($this->_transaction->toArray());
				
			} else {
				throw new \Exception('Posted data response as array is required.');
			}
		
	}
	

	public function processSplitPayment(){
		$amount = $this->_order->getOrderCurrency()->formatPrecision($this->splitPayment->getAmountToPay(), 2,[],false);
		$this->_doTransactionMessage(__('Split Payment #%1. %2 %3.',$this->splitPayment->getId(),$amount,$this->_transaction->getMessage()));
		return $this;
	}

	
	protected function canProcessTransaction(){
		
		
		//Test if status is already processed
		$savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
		if(is_array($savedStatues) && isset($savedStatues[$this->_transaction->getStatus()]))
		{
			return false;
		}
		
		switch ($this->_transaction->getStatus()){
			case TransactionStatus::EXPIRED: //114
				
				if(in_array($this->_order->getStatus(),array(Config::STATUS_AUTHORIZED))){
					return true;
				}
				
				break;
			case  TransactionStatus::AUTHORIZED: //116
				if($this->_order->getState() == \Magento\Sales\Model\Order::STATE_NEW ||
					$this->_order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT ||
					$this->_order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW ||
					in_array($this->_order->getStatus(),array(Config::STATUS_AUTHORIZATION_REQUESTED))){
					return true;
				}
				break;
			case TransactionStatus::CAPTURE_REQUESTED: //117
				if(!$this->_order->hasInvoices() || $this->_order->getBaseTotalDue() == $this->_order->getBaseGrandTotal())
				{
					return true;
				}
				break;
			default:
				return true;
		}
		
		return false;
		
	}
	
	
	public function processTransaction(){



		if($this->isSplitPayment){
			$this->processSplitPayment();
			return $this;
		}
		

		if(!$this->canProcessTransaction()){
			return $this;
		}

		
		/**
		 * Begin transaction to lock this order record during update
		 */
		$this->orderResource->getConnection()->beginTransaction();
		
		$selectForupdate = $this->orderResource->getConnection()->select()
		->from($this->orderResource->getMainTable())->where($this->orderResource->getIdFieldName() . '=?', $this->_order->getId())
		->forUpdate(true);
		
		//Execute for update query
		$this->orderResource->getConnection()->fetchOne($selectForupdate);
		
		
		//Write about notification in order history
		$this->_doTransactionMessage("Status code: " . $this->_transaction->getStatus());
		

		switch ($this->_transaction->getStatus()){
			case TransactionStatus::BLOCKED: //110
				$this->_setFraudDetected();
			case TransactionStatus::DENIED: //111
				$this->_doTransactionDenied();
				break;
			case TransactionStatus::AUTHORIZED_AND_PENDING: //112
			case TransactionStatus::PENDING_PAYMENT: //200
				$this->_setFraudDetected();
				$this->_doTransactionAuthorizedAndPending();
				break;
			case TransactionStatus::AUTHORIZATION_REQUESTED: //142
				$this->_changeStatus(Config::STATUS_AUTHORIZATION_REQUESTED);
				break;
			case TransactionStatus::REFUSED: //113
			case TransactionStatus::CANCELLED: //115 Cancel order and transaction
			case TransactionStatus::AUTHORIZATION_REFUSED: //163
			case TransactionStatus::CAPTURE_REFUSED: //173
				$this->_doTransactionFailure();
				break;
			case TransactionStatus::EXPIRED: //114 Hold order, the merchant can unhold and try a new capture
				$this->_doTransactionVoid();
				break;
			case TransactionStatus::AUTHORIZED: //116
				$this->_doTransactionAuthorization();
				break;
			case TransactionStatus::CAPTURE_REQUESTED: //117
				$this->_doTransactionCaptureRequested();
				//If status Capture Requested is not configured to validate the order, we break.
				if(((int)$this->_order->getPayment()->getMethodInstance()->getConfigData('hipay_status_validate_order') == 117) === false )
					break;
			case TransactionStatus::CAPTURED: //118
			case TransactionStatus::PARTIALLY_CAPTURED: //119				
				
				if(($this->_order->getStatus() == $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_accepted') ) ||
						//If status Capture Requested is configured to validate the order and is a direct capture notification (118), we break because order is already validate.
						((int)$this->_order->getPayment()->getMethodInstance()->getConfigData('hipay_status_validate_order') == 117) === true 
								&& (int)$this->_transaction->getStatus() == 118 
								&& !in_array(strtolower($this->_order->getPayment()->getCcType()),array('amex','ae')))
				{
					break;
				}
				
				//If is split payment case, grand total is different to captured amount
				//So we skip fraud detection in this case
				$this->_doTransactionCapture($this->isSplitPayment ?: false);
				/**
				 * save token and credit card informations encryted
				 */
				$this->_saveCc();
				
				/**
				 * save split payments
				 */
				if(!$this->orderAlreadySplit()){				
					$this->insertSplitPayment();
				}
				
				break;
			case TransactionStatus::REFUND_REQUESTED: //124
				$this->_doTransactionRefundRequested();
				break;
			case TransactionStatus::REFUNDED: //125
			case TransactionStatus::PARTIALLY_REFUNDED: //126
				$this->_doTransactionRefund();
				break;
			case TransactionStatus::REFUND_REFUSED: //165
				$this->_order->setStatus(Config::STATUS_REFUND_REFUSED);
				$this->_order->save();
			case TransactionStatus::CREATED: //101
			case TransactionStatus::CARD_HOLDER_ENROLLED: //103
			case TransactionStatus::CARD_HOLDER_NOT_ENROLLED: //104
			case TransactionStatus::UNABLE_TO_AUTHENTICATE: //105
			case TransactionStatus::CARD_HOLDER_AUTHENTICATED: //106
			case TransactionStatus::AUTHENTICATION_ATTEMPTED: //107
			case TransactionStatus::COULD_NOT_AUTHENTICATE: //108
			case TransactionStatus::AUTHENTICATION_FAILED: //109
			case TransactionStatus::COLLECTED: //120
			case TransactionStatus::PARTIALLY_COLLECTED: //121
			case TransactionStatus::SETTLED: //122
			case TransactionStatus::PARTIALLY_SETTLED: //123
			case TransactionStatus::CHARGED_BACK: //129
			case TransactionStatus::DEBITED: //131
			case TransactionStatus::PARTIALLY_DEBITED: //132
			case TransactionStatus::AUTHENTICATION_REQUESTED: //140
			case TransactionStatus::AUTHENTICATED: //141
			case TransactionStatus::ACQUIRER_FOUND: //150
			case TransactionStatus::ACQUIRER_NOT_FOUND: //151
			case TransactionStatus::CARD_HOLDER_ENROLLMENT_UNKNOWN: //160
			case TransactionStatus::RISK_ACCEPTED: //161
				$this->_doTransactionMessage();
				break;
		}
		
		//Save status infos
		$this->saveHiPayStatus();
		
		//Send commit to unlock order table
		$this->orderResource->getConnection()->commit();
		
		return $this;
	}
	
	/**
	 * Save infos of statues processed 
	 */
	protected function saveHiPayStatus(){
		
		$lastStatus = $this->_transaction->getStatus();
		$savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
		if(!is_array($savedStatues)){
			$savedStatues = [];
		}
		
		if(isset($savedStatues[$lastStatus])){
			return;
		}
		
		$savedStatues[$lastStatus] = [
				'saved_at' => new \DateTime(),
				'state'	   => $this->_transaction->getState(),
				'status'   => $lastStatus
		];
		
		//Save array of statues already processed
		$this->_order->getPayment()->setAdditionalInformation('saved_statues',$savedStatues);
		
		//Save the last status
		$this->_order->getPayment()->setAdditionalInformation('last_status',$lastStatus);
		$this->_order->save();
		
	}
	
	protected function orderAlreadySplit(){
		/** @var $splitPayments \HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment\Collection */
		$splitPayments = $this->spFactory->create()->getCollection()->addFieldToFilter('order_id',$this->_order->getId());
		if($splitPayments->count()){
			return true;
		}
		return false;
	}
	
	protected function insertSplitPayment(){
		//Check if it is split payment and insert it
		$profileId=0;
		if(($profileId = (int)$this->_order->getPayment()->getAdditionalInformation('profile_id')))
		{
			
			$profile = $this->ppFactory->create()->load($profileId);
			if($profile->getId()){
				
				$splitAmounts = $profile->splitAmount($this->_order->getBaseGrandTotal());
				
				/** @var $splitPayment \HiPay\FullserviceMagento\Model\SplitPayment */
				for ($i=0;$i<count($splitAmounts);$i++){
					
					$splitPayment = $this->spFactory->create();
					
					$splitPayment->setAmountToPay($splitAmounts[$i]['amountToPay']);
					$splitPayment->setAttempts($i==0 ? 1 : 0);
					$splitPayment->setCardToken($this->_transaction->getPaymentMethod()->getToken());
					$splitPayment->setCustomerId($this->_order->getCustomerId());
					$splitPayment->setDateToPay($splitAmounts[$i]['dateToPay']);
					$splitPayment->setMethodCode($this->_order->getPayment()->getMethod());
					$splitPayment->setRealOrderId($this->_order->getIncrementId());
					$splitPayment->setOrderId($this->_order->getId());
					$splitPayment->setStatus($i==0 ? SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE : SplitPayment::SPLIT_PAYMENT_STATUS_PENDING );
					$splitPayment->setBaseGrandTotal($this->_order->getBaseGrandTotal());
					$splitPayment->setBaseCurrencyCode($this->_order->getBaseCurrencyCode());
					$splitPayment->setProfileId($profileId);
					
					try {
						$splitPayment->save();
					} catch (Exception $e) {
						if($this->_order->canHold()){
							$this->_order->hold();
						}
						$this->_doTransactionMessage($e->getMessage());
					}
				}

			}
			else{
				if($this->_order->canHold()){
					$this->_order->hold();
				}
				$this->_doTransactionMessage(__('Order holded because split payments was not saved!'));
			}
		}
	}
	
	protected function _canSaveCc(){
		return (bool)in_array($this->_transaction->getPaymentProduct(),['visa','american-express','mastercard','cb']) 
					&& $this->_order->getPayment()->getAdditionalInformation('create_oneclick');
	}
	
	/**
	 * @return bool|\HiPay\FullserviceMagento\Model\Card 
	 */
	protected function _saveCc(){
		
		if($this->_canSaveCc())
		{
			$token = $this->_transaction->getPaymentMethod()->getToken();
			if(!$this->_cardTokenExist($token))
			{
				/** @var $card \HiPay\FullserviceMagento\Model\Card */
				$card = $this->_cardFactory->create();
				/** @var $paymentMethod \HiPay\Fullservice\Gateway\Model\PaymentMethod */
				$paymentMethod = $this->_transaction->getPaymentMethod();
				$paymentProduct = $this->_transaction->getPaymentProduct();
				$card->setCcToken($token);
				$card->setCustomerId($this->_order->getCustomerId());
				$card->setCcExpMonth($paymentMethod->getCardExpiryMonth());
				$card->setCcExpYear($paymentMethod->getCardExpiryYear());
				$card->setCcNumberEnc($paymentMethod->getPan());
				$card->setCcType($paymentProduct);
				$card->setCcStatus(\HiPay\FullserviceMagento\Model\Card::STATUS_ENABLED);
				$card->setName(sprintf(__('Card %s - %s'),$paymentMethod->getBrand(),$paymentMethod->getPan()));
				

				try {
					
					return $card->save();
				} 
				catch (\Exception $e) {
					$this->_generateComment(__("Card not registered! Due to: %s",$e->getMessage()),true);
				}
			}
		}
		
		return false;
		
	}
	
	protected function _cardTokenExist($token)
	{
		return (bool)$this->_cardFactory->create()->load($token,'cc_token')->getId();
	}
	
	/**
	 * Check Fraud Screenig result for fraud detection
	 */
	protected function _setFraudDetected()
	{
	
		if(!is_null($fraudSreening = $this->_transaction->getFraudScreening())){
			if($fraudSreening->getResult() && $fraudSreening->getScoring()){
				$payment = $this->_order->getPayment();
				$payment->setIsFraudDetected(true);
				
				$payment->setAdditionalInformation('fraud_type',$fraudSreening->getResult() );
				$payment->setAdditionalInformation('fraud_score',$fraudSreening->getScoring());
				$payment->setAdditionalInformation('fraud_review',$fraudSreening->getReview());
				
				$isDeny = ($fraudSreening->getResult()  != 'challenged' || $this->_transaction->getState() == TransactionState::DECLINED);
				
				if(!$isDeny){
					$this->fraudReviewSender->send($this->_order);
				}
				else{
					$this->fraudDenySender->send($this->_order);
				}

			}
		}
	}
	
	protected function _changeStatus($status,$comment = "",$addToHistory = true,$save=true){
		$this->_generateComment($comment,$addToHistory);
		$this->_order->setStatus($status);
		
		if($save)$this->_order->save();
	}
	
	/**
	 * Add status to order history
	 *
	 * @return void
	 */
	protected function _doTransactionMessage($message = "")
	{
		if($this->_transaction->getReason() != ""){
			$message .= __(" Reason: %1",$this->_transaction->getReason());
		}
		$this->_generateComment($message,true);
		$this->_order->save();
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
								->setCcTransId($this->_transaction->getTransactionReference())
								->setParentTransactionId($parentTransactionId)
								->setIsTransactionClosed($isCompleteRefund)
								->registerRefundNotification(-1 * $this->_transaction->getRefundedAmount());
		
		$orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_REFUND_REQUESTED;
		
		if($this->_transaction->getStatus() == TransactionStatus::PARTIALLY_REFUNDED){
			$orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_PARTIALLY_REFUNDED;
		}
		
		$this->_order->setStatus($orderStatus);
								
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
		
		$this->_order->getPayment()->setPreparedMessage($this->_generateComment(''))
		->setTransactionId($this->_transaction->getTransactionReference() . "-authorization-pending")
		->setCcTransId($this->_transaction->getTransactionReference())
		->setCurrencyCode($this->_transaction->getCurrency())
		->setIsTransactionClosed(0)
		->registerAuthorizationNotification((float)$this->_transaction->getAuthorizedAmount());
		
		$this->_order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
		$this->_doTransactionMessage("Transaction is fraud challenged. Waiting for accept or deny action.");
		$this->_order->save();

		

	}

	

	/**
	 * Process capture requested payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionCaptureRequested()
	{
		$this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
		$this->_changeStatus(Config::STATUS_CAPTURE_REQUESTED,'Capture Requested.');
	}
	
	/**
	 * Process refund requested payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionRefundRequested()
	{
		$this->_changeStatus(Config::STATUS_REFUND_REQUESTED,'Refund Requested.');
	}
	
	/**
	 * Process refund refused payment notification
	 *
	 * @return void
	 */
	protected function _doTransactionRefundRefused()
	{
		$this->_changeStatus(Config::STATUS_REFUND_REFUSED,'Refund Refused.');
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
						->setCcTransId($this->_transaction->getTransactionReference())
						->setNotificationResult(true)
						->setIsTransactionClosed(true)
						->deny(false);
		
		$orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_refused');
		$this->_order->setStatus($orderStatus);
		
		$this->_order->save();
	}
	
	/**
	 * Treat failed payment as order cancellation
	 *
	 * @return void
	 */
	protected function _doTransactionFailure()
	{
		$this->_order->registerCancellation($this->_generateComment(''));
		$orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_refused');
		if($this->_transaction->getStatus() == TransactionStatus::CANCELLED){
			$orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_canceled');
		}
		$this->_order->setStatus($orderStatus);
		$this->_order->save();
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
				->setCcTransId($this->_transaction->getTransactionReference())
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
		$payment->setCcTransId($this->_transaction->getTransactionReference());
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
		
		$orderStatus = $payment->getMethodInstance()->getConfigData('order_status_payment_accepted');
		
		if($this->_transaction->getStatus() == TransactionStatus::PARTIALLY_CAPTURED){
			$orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_PARTIALLY_CAPTURED;
		}
		
		$this->_order->setStatus($orderStatus);
		
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
	 * Process voided authorization
	 *
	 * @return void
	 */
	protected function _doTransactionVoid()
	{
	
		$parentTransactionId = $payment->getLastTransId();
	
		$this->_order->getPayment()
		->setPreparedMessage($this->_generateComment(''))
		->setParentTransactionId($parentTransactionId)
		->registerVoidNotification();
	
		$this->_order->save();
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