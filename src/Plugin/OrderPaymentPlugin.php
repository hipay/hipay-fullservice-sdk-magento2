<?php
/**
 * HiPay Fullservice Magento
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
namespace HiPay\FullserviceMagento\Plugin;

use Magento\Sales\Model\Order;
use HiPay\FullserviceMagento\Model\Config;

/**
 * HiPay Plugin
 *
 * Override Methods in \Magento\Sales\Model\Order\Payment
 * 
 * Used to set custom state and status to the order
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class OrderPaymentPlugin {
	
	/**
	 * Run HiPay accept payment
	 * Used to set custom status and state when order is accepted
	 *
	 * @param \Magento\Sales\Model\Order\Payment $subject
	 * @param callable $proceed
	 *
	 * @return \Magento\Sales\Model\Order\Payment
	 */
	public function aroundAccept(\Magento\Sales\Model\Order\Payment $subject,callable $proceed){
		
		if($this->isHipayMethod($subject->getMethod())){
			$transactionId = $subject->getLastTransId();
			
			/** @var \Magento\Payment\Model\Method\AbstractMethod $method */
			$method = $subject->getMethodInstance();
			$method->setStore($subject->getOrder()->getStoreId());
			if ($method->acceptPayment($subject)) {
				
				//Do nothing let notification to change status order
				
				//Set Authorization requested, if order status still un payment review
				/*if($subject->getOrder()->getState() == Order::STATE_PAYMENT_REVIEW) {
					$message = __('Authorization was requested.');
					$subject->getOrder()
						->setState(Order::STATE_PROCESSING)
					->addStatusHistoryComment($message);
					
				}*/
				
			} else {
				$message = $subject->_appendTransactionToMessage(
						$transactionId,
						$subject->prependMessage(__('There is no need to approve this payment.'))
						);
				$subject->setOrderStatePaymentReview($message, $transactionId);
			}
		}
		else{
			$proceed();
		}
		
		return $subject;
	}
	
	/**
	 * Run HiPay deny payment
	 * Used to set custom status and state when order is denied
	 *
	 * @param \Magento\Sales\Model\Order\Payment $subject
	 * @param callable $proceed
	 *
	 * @return \Magento\Sales\Model\Order\Payment
	 */
	public function aroundDeny(\Magento\Sales\Model\Order\Payment $subject,callable $proceed,$isOnline = true){
	
		if($this->isHipayMethod($subject->getMethod())){
			//$transactionId = $isOnline ? $subject->getLastTransId() : $subject->getTransactionId();
			
			if ($isOnline) {
				/** @var \Magento\Payment\Model\Method\AbstractMethod $method */
			
				$method = $subject->getMethodInstance();
				$method->setStore($subject->getOrder()->getStoreId());
				$method->denyPayment($subject);
				
			} else {
				$proceed($isOnline);
			}
			
			
			// @var \Magento\Payment\Model\Method\AbstractMethod $method //
			
		}
		else{
			$proceed($isOnline);
		}
	
		return $subject;
	}
	
	/**
	 * 
	 * @param string $method
	 * @return bool
	 */
	protected function isHipayMethod($method){
		
		if(strpos($method,'hipay') !== false)
		{ 
			return true;
		}
		
		return false;
	}
	
}