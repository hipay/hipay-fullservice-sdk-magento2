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
use Magento\Framework\App\Action\Context;

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
	

	
	protected function _validateSignature()
	{
	    $signature= $this->getRequest()->getServerValue('HTTP_X_ALLOPASS_SIGNATURE');
		//@TODO check signature passphrase
		
		return true;
	}
	
	/**
	 * @return void
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * */
     public function execute(){
     	//ini_set('display_errors',1);
     	//error_reporting(E_ALL);
     	$params = $this->getRequest()->getPostValue();
     	
     	try {
     		$this->_logger->info("Debug notification");
     		$this->_logger->info(print_r($params,true));
     		
     		/* @var $notify \Hipay\FullserviceMagento\Model\Notify */
     		$notify = $this->_objectManager->create('\Hipay\FullserviceMagento\Model\Notify',['params'=>['response'=>$params]]);
     		$notify->processTransaction();
     		
     		
     	} catch (\Exception $e) {
     		$this->_logger->error($e->getMessage());
     		$this->getResponse()->setStatusHeader(500, '1.1', $e->getMessage())->sendResponse();
     	}

     	$this->getResponse()->setBody('OK')->sendResponse();

	 }
	 
	 
}