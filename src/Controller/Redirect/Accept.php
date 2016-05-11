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
class Accept extends Fullservice {
	
	/**
	 * @return void
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * */
	public function execute(){
		//MO/TO case
		if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
			
			$this->messageManager->addSuccess(__('Thank you for your order. You will receveive a confirmation email soon.'));
			return $this->resultRedirectFactory->create()->setPath('checkout/cart');
		}
		
		$this->_redirect('checkout/onepage/success');

	}
	
}