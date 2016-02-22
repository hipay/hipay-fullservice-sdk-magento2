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
namespace HiPay\FullserviceMagento\Model\Gateway;


use HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use HiPay\FullserviceMagento\Model\Request\Type\Factory as RequestFactory;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory; 
use HiPay\Fullservice\HTTP\SimpleHTTPClient;
use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\Enum\Transaction\Operation;

class Manager {
	
	/**
	 * Order
	 *
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	/**
	 * 
	 * @var \HiPay\Fullservice\Gateway\Client\GatewayClient $_gateway
	 */
	protected $_gateway;
	
	/**
	 * 
	 * @var HiPay\FullserviceMagento\Model\Config $_config
	 */
	protected $_config;
	
	/**
	 * 
	 * @var ConfigFactory
	 */
	protected $_configFactory;
	
	/**
	 *
	 * @var RequestFactory
	 */
	protected $_requestFactory;
	
	public function __construct(
			RequestFactory $requestfactory,
			ConfigFactory $configFactory,
			$params = []
			
			){
		$this->_configFactory = $configFactory;
		$this->_requestFactory = $requestfactory;
		
		if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
			$this->_order = $params['order'];
		} else {
			throw new \Exception('Order instance is required.');
		}
		$methodCode = $this->_order->getPayment()->getMethod();
		$storeId = $this->_order->getStoreId();
		$this->_config = $this->_configFactory->create(['params'=>['methodCode'=>$methodCode,'storeId'=>$storeId]]);
		$clientProvider = new SimpleHTTPClient($this->_config);
		$this->_gateway = new GatewayClient($clientProvider);
	}
	
	/**
	 * @return \HiPay\Fullservice\HTTP\ClientProvider
	 */
	public function getClientProvider(){
		return $this->_gateway->getClientProvider();
	}
	
	/**
	 * @return  ConfigurationInterface
	 */
	public function getConfiguration(){
		return $this->_config;
	}
	
	
	
	public function requestHostedPaymentPage(){
		
		$hpp = $this->_getRequestObject('\HiPay\FullserviceMagento\Model\Request\HostedPaymentPage');
		 
		$hppModel = $this->_gateway->requestHostedPaymentPage($hpp);
		
		return $hppModel;
	}
	
	public function requestOperationCapture($amount=null){
		
		return $this->_requestOperation(Operation::CAPTURE, $amount);
	}
	
	public function requestOperationRefund($amount=null){
		
		return $this->_requestOperation(Operation::REFUND, $amount);
	}
	
	public function requestOperationAcceptChallenge(){
	
		return $this->_requestOperation(Operation::ACCEPT_CHALLENGE);;
	}
	

	public function requestOperationDenyChallenge(){
	
		return $this->_requestOperation(Operation::DENY_CHALLENGE);
	}
	
	private function cleanTransactionValue($transactionReference){
		list($tr,) = explode("-", $transactionReference);
		return $tr;
	}
	
	protected function _getPayment(){
		return $this->_order->getPayment();
	}
	
	protected function _getRequestObject($requestClassName){
		return $this->_requestFactory->create($requestClassName,$this->_getRequestParameters())->getRequestObject();	
	}
	
	protected function _getRequestParameters(){
		return [
				'params' => [
						'order' => $this->_order,
						'config' => $this->getConfiguration(),
				],
		];	
	}
	
	protected function _requestOperation($operationType,$amount=null,$operationId=null){
		
		$transactionReference = $this->cleanTransactionValue($this->_getPayment()->getLastTransId());
		if(is_null($operationId)){			
			$operationId = $this->_order->getIncrementId() ."-" . $operationType ."-manual";
		}
		$opModel = $this->_gateway->requestMaintenanceOperation($operationType, $transactionReference, $amount,$operationId);
		return$opModel;
	}

	
	
}
