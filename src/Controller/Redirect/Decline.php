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
namespace HiPay\FullserviceMagento\Controller\Redirect;

use HiPay\FullserviceMagento\Controller\Fullservice;
/**
 *
 * @author kassim
 *        
 */
class Decline extends Fullservice {
	
	/**
	 * @return void
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * */
	public function execute(){
		
		ini_set('display_errors',1);
		error_reporting(E_ALL);
		
		$lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
		if($lastOrderId){
			/** @var $order  \Magento\Sales\Model\Order */
			$order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($lastOrderId);
			if($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')){
		
				/* @var $cart \Magento\Checkout\Model\Cart */
				$cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
				$items = $order->getItemsCollection();
				foreach ($items as $item) {
					try {
						$cart->addOrderItem($item);
					} catch (\Magento\Framework\Exception\LocalizedException $e) {
						if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
							$this->messageManager->addNotice($e->getMessage());
						} else {
							$this->messageManager->addError($e->getMessage());
						}
		
					} catch (\Exception $e) {
						$this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
		
					}
				}
		
				$cart->save();
			}
		}
		
		$this->_checkoutSession->setErrorMessage(__('Your order was declined.'));
		$this->_redirect('checkout/onepage/failure');

	}
	
}