<?php
/**
 * HiPay Fullservice Magento
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
namespace HiPay\FullserviceMagento\Controller\Notify;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\Action\Context;

/**
 * Notification controller
 * Manage order validation and modification
 * 
 * Is protected by secret passphare (See \HiPay\FullserviceMagento\Observer\CheckHttpSignatureObserver.php)
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Index extends AppAction {
	
	/**
	 * 
	 * @var  \Psr\Log\LoggerInterface $_logger
	 */
	protected $_logger;
	
	
	/**
	 * @param Context $context
	 */
	public function __construct(
			Context $context,
			\Psr\Log\LoggerInterface $_logger
			){
		parent::__construct($context);
		
		$this->_logger = $_logger;
		
	}

	/**
	 * @return void
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * */
     public function execute(){
     	//ini_set('display_errors',1);
     	//error_reporting(E_ALL);
     	
     	$params = $this->getRequest()->getPost()->toArray();
     	
     	try {
     		
     		/* @var $notify \HiPay\FullserviceMagento\Model\Notify */
     		$notify = $this->_objectManager->create('\HiPay\FullserviceMagento\Model\Notify',['params'=>['response'=>$params]]);
     		$notify->processTransaction();
     		
     		
     	} catch (\Exception $e) {
     		$this->_logger->error($e->getMessage());
     		$this->getResponse()->setStatusHeader(400, '1.1', $e->getMessage())->sendResponse();
     	}

     	$this->getResponse()->setBody('OK')->sendResponse();

	 }
	 
	 /**
	  * Retrieve request object
	  *
	  * @return \Magento\Framework\App\Request\Http
	  */
	 public function getRequest()
	 {
	 	return $this->_request;
	 }
	 
	 /**
	  * Retrieve response object
	  *
	  * @return \Magento\Framework\App\Response\Http
	  */
	 public function getResponse()
	 {
	 	return $this->_response;
	 }
	 
	 
}