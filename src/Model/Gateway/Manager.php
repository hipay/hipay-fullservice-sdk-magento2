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
namespace Hipay\FullserviceMagento\Model\Gateway;


use Hipay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use Hipay\FullserviceMagento\Model\Request\Type\Factory as RequestFactory;
use Hipay\FullserviceMagento\Model\Config\Factory as ConfigFactory; 
use Hipay\Fullservice\HTTP\GuzzleClient;
use Hipay\Fullservice\Gateway\Client\GatewayClient;
use Hipay\Fullservice\Enum\Transaction\Operation;

class Manager {
	
	/**
	 * Order
	 *
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	/**
	 * 
	 * @var \Hipay\Fullservice\Gateway\Client\GatewayClient $_gateway
	 */
	protected $_gateway;
	
	/**
	 * 
	 * @var Hipay\FullserviceMagento\Model\Config $_config
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
		$clientProvider = new GuzzleClient($this->_config);
		$this->_gateway = new GatewayClient($clientProvider);
	}
	
	/**
	 * @return \Hipay\Fullservice\HTTP\ClientProvider
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
		
		$hpp = $this->_getRequestObject('\Hipay\FullserviceMagento\Model\Request\HostedPaymentPage');
		 
		$hppModel = $this->_gateway->requestHostedPaymentPage($hpp);
		
		return $hppModel;
	}
	
	public function requestOperationCapture($amount){
		
		return $this->_requestOperation(Operation::CAPTURE, $amount);
	}
	
	public function requestOperationRefund($amount){
		
		return $this->_requestOperation(Operation::REFUND, $amount);
	}
	
	public function requestOperationAcceptChallenge($amount){
	
		return $this->_requestOperation(Operation::ACCEPT_CHALLENGE, $amount);;
	}
	

	public function requestOperationDenyChallenge($amount){
	
		return $this->_requestOperation(Operation::DENY_CHALLENGE, $amount);
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
	
	protected function _requestOperation($operationType,$amount,$operationId=null){
		
		$transactionReference = $this->cleanTransactionValue($this->_getPayment()->getLastTransId());
		if(is_null($operationId)){			
			$operationId = $this->_order->getIncrementId() ."-" . $operationType ."-manual";
		}
		$opModel = $this->_gateway->requestMaintenanceOperation($operationType, $transactionReference, $amount,$operationId);
		return$opModel;
	}

	
	
}