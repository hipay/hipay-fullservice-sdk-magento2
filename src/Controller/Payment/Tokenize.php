<?php
/**
 * HiPay fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Controller\Payment;


/**
 * @deprecated
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Tokenize extends \HiPay\FullserviceMagento\Controller\Fullservice
{	


    /**
     * Call Secure vault to generate a new token
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
        	
        	$data = json_decode($this->getRequest()->getContent());
        	if($data){
        		$methodCode = $data->method;
        		$cardInfo = $data;
	        	$vaultManager = $this->_vaultManagerFactory->create($methodCode);
	        	$ccNumber = isset($cardInfo->cc_number) ? $cardInfo->cc_number : "";
	        	$ccExpMonth = isset($cardInfo->cc_exp_month) ?  sprintf('%02d',$cardInfo->cc_exp_month ) : "";
	        	$ccExpYear = isset($cardInfo->cc_exp_year) ? $cardInfo->cc_exp_year : "";
	        	$ccCid = isset($cardInfo->cc_cid) ?$cardInfo->cc_cid : "";
	        	$ccCardHolder = isset($cardInfo->cc_card_holder) ? $cardInfo->cc_card_holder : "";
	        	
	        	$tokenModel = $vaultManager->requestGenerateToken($ccNumber,$ccExpMonth, $ccExpYear,$ccCid,$ccCardHolder);
	        	
	        	$this->getResponse()->representJson($tokenModel->toJson());
	        	
        		
        	}


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        	
        	$this->getResponse()->representJson(json_encode(array("code"=>$e->getCode(), "message"=>$e->getMessage())));
        	$this->getResponse()->setStatusHeader(400, '1.1');

        } catch (\Exception $e) {
        	$this->getResponse()->representJson(json_encode(array("code"=>$e->getCode(), "message"=>__('We can\'t place the order.'))));
        	$this->getResponse()->setStatusHeader(400, '1.1');
        	$this->logger->addDebug($e->getMessage());
        
          
        }

    }


 
}
