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
use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;
use Magento\Framework\Exception\LocalizedException;
use HiPay\Fullservice\Request\RequestSerializer;

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
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\FullserviceMethod $_methodInstance
	 */
	protected $_methodInstance;

	
	public function __construct(
			RequestFactory $requestfactory,
			ConfigFactory $configFactory,
		    \Magento\Payment\Helper\Data $paymentHelper,
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
		
		$this->_methodInstance = $paymentHelper->getMethodInstance($methodCode);
		
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
	
	
	/**
	 * 
	 */
	public function requestHostedPaymentPage(){
		
		
		//Init cardTokenPaymentMethod request
		$cardTokenPaymentMethod = new CardTokenPaymentMethod();
		$cardTokenPaymentMethod->authentication_indicator = $this->_config->getValue('authentication_indicator');
		$cardTokenPaymentMethod->cardtoken = "";
		$cardTokenPaymentMethod->eci = 7;
		
		//Merge params
		$params = $this->_getRequestParameters();
		$params['params']['paymentMethod'] = $this->_getPaymentMethodRequest();
		
		/** @var $hpp \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest  */
		$hpp = $this->_getRequestObject('\HiPay\FullserviceMagento\Model\Request\HostedPaymentPage',$params);
		$this->_debug($this->_requestToArray($hpp));
		
		/** @var $hppModel \HiPay\Fullservice\Gateway\Model\HostedPaymentPage */
		$hppModel = $this->_gateway->requestHostedPaymentPage($hpp);
		$this->_debug($hppModel->toArray());
		
		return $hppModel;
	}
	
	/**
	 * 
	 */
	public function requestNewOrder(){
		
		//Merge params
		$params = $this->_getRequestParameters();
		$params['params']['paymentMethod'] =  $this->_getPaymentMethodRequest();;
		
		$orderRequest = $this->_getRequestObject('\HiPay\FullserviceMagento\Model\Request\Order',$params);
		$this->_debug($this->_requestToArray($orderRequest));
		
		//Request new order transaction
		$transaction = $this->_gateway->requestNewOrder($orderRequest);
		$this->_debug($transaction->toArray());
		
		
		return $transaction;
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
	
	
	protected function _getPaymentMethodRequest(){
		$className = $this->_methodInstance->getConfigData('payment_method');
		if(!empty($className)){
			return $this->_getRequestObject($className);
		}
	}
	
	/**
	 * 
	 * @param \HiPay\Fullservice\Request\RequestInterface $request
	 * @return []
	 */
	protected function _requestToArray(\HiPay\Fullservice\Request\RequestInterface $request){
		
		return (new RequestSerializer($request))->toArray();
	}
	
	protected function _debug($debugData){
		$this->_methodInstance->debugData($debugData);
	}
	
	protected function _getPayment(){
		return $this->_order->getPayment();
	}
	
	protected function _getRequestObject($requestClassName,array $params=null){
		if(is_null($params)){
			$params = $this->_getRequestParameters();
		}
		return $this->_requestFactory->create($requestClassName,$params)->getRequestObject();	
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
