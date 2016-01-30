<?php
/*
 * Hipay fullservice Magento2
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
namespace Hipay\FullserviceMagento\Controller\Hosted;


use Hipay\Fullservice\HTTP\Configuration\Configuration;
use Hipay\Fullservice\HTTP\GuzzleClient;
use Hipay\Fullservice\Gateway\Client\GatewayClient;
use Magento\Framework\Controller\ResultFactory;

class PlaceOrder extends \Hipay\FullserviceMagento\Controller\Fullservice
{
	
	/**
	 *
	 * @var \Hipay\FullserviceMagento\Model\Checkout\Hosted\Checkout
	 */
	protected $_checkout;
	
	/**
	 * Checkout mode type
	 *
	 * @var string
	 */
	protected $_checkoutType = 'Hipay\FullserviceMagento\Model\Checkout\Hosted\Checkout';
	
	
	/**
	 * Config mode type
	 *
	 * @var string
	 */
	protected $_configType = 'Hipay\FullserviceMagento\Model\Config';
	
	/**
	 * Config method type
	 *
	 * @var string
	 */
	protected $_configMethod = \Hipay\FullserviceMagento\Model\HostedMethod::HIPAY_HOSTED_METHOD_CODE;


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
    	//die(ini_get('memory_limit'));
        try {
        	
        	
        	
            $order = $this->_getCheckoutSession()->getLastRealOrder();
			
           if(!$order->getId()){
            	throw new \Magento\Framework\Exception\LocalizedException(
            			__('We can\'t place the order.')
            			);
            }
           
            $configuration = new Configuration($this->_config->getApiUsername(), $this->_config->getApiPassword(),$this->_config->getValue('env'));
            $clientProvider = new GuzzleClient($configuration);

            $gateway = new GatewayClient($clientProvider);
            $parameters = [
            		'params' => [
            				'order' => $order,
            				'config' => $this->_config,
            		],
            ];
            
            $hpp = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\HostedPaymentPage',$parameters)->getRequestObject();
        	
            $hppModel = $gateway->requestHostedPaymentPage($hpp);
            //@TODO catch sdk exception
            $this->logger->debug($hppModel->getForwardUrl());
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
