<?php
/*
 * HiPay fullservice Magento2
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
namespace HiPay\FullserviceMagento\Controller\Hosted;



class AfterPlaceOrder extends \HiPay\FullserviceMagento\Controller\Fullservice
{	


    /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
    	ini_set('display_errors', 1);
    	error_reporting(E_ALL | E_STRICT);
    	
        try {
        	
        	
           //Retieve last order increment id
           $order = $this->_getCheckoutSession()->getLastRealOrder();
			
           if(!$order->getId()){
            	throw new \Magento\Framework\Exception\LocalizedException(
            			__('We can\'t place the order.')
            			);
            }
           
            //Create gateway manage with order data
            $gateway = $this->_gatewayManagerFactory->create($order);
        	
            //Call fullservice api to get hosted page url
            $hppModel = $gateway->requestHostedPaymentPage();
			
            //Redirect to hosted page
            $this->getResponse()->setRedirect($hppModel->getForwardUrl());
            return;


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );

        } catch (\Exception $e) {
        	$this->logger->addDebug($e->getMessage());
        	$this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the order.')
            );
          
        }
        $this->_redirect('checkout/cart');
    }


 
}
