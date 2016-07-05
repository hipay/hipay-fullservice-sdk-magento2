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
namespace HiPay\FullserviceMagento\Model\Gateway;


use HiPay\FullserviceMagento\Model\Request\Type\Factory as RequestFactory;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory; 
use HiPay\Fullservice\HTTP\SimpleHTTPClient;
use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\Enum\Transaction\Operation;
use HiPay\Fullservice\Request\RequestSerializer;

/**
 * Gateway Manager Class
 * 
 * HiPay Fullservice SDK is used by the manager
 * So, all api call are centralized here
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
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
		
		$this->_config = $this->_configFactory->create(['params'=>['methodCode'=>$methodCode,'storeId'=>$storeId,'order'=>$this->_order]]);
		
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
	 * @return  \HiPay\FullserviceMagento\Model\Config
	 */
	public function getConfiguration(){
		return $this->_config;
	}
	
	
	/**
	 * 
	 */
	public function requestHostedPaymentPage(){
		
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
		
		//If is admin area set mo/to value to payment additionnal informations
		if($this->getConfiguration()->isAdminArea()){
			$this->_order->getPayment()->setAdditionalInformation('is_moto',1);
			$this->_order->save();
		}
		
		
		return $transaction;
	}
	
	/**
	 *
	 * @return \HiPay\Fullservice\Gateway\Model\Operation
	 */
	public function requestOperationCapture($amount=null){
		
		return $this->_requestOperation(Operation::CAPTURE, $amount);
	}
	
	/**
	 *
	 * @return \HiPay\Fullservice\Gateway\Model\Operation
	 */
	public function requestOperationRefund($amount=null){
		
		return $this->_requestOperation(Operation::REFUND, $amount);
	}
	
	/**
	 *
	 * @return \HiPay\Fullservice\Gateway\Model\Operation
	 */
	public function requestOperationAcceptChallenge(){
	
		return $this->_requestOperation(Operation::ACCEPT_CHALLENGE);;
	}
	
	/**
	 *
	 * @return \HiPay\Fullservice\Gateway\Model\Operation
	 */
	public function requestOperationDenyChallenge(){
	
		return $this->_requestOperation(Operation::DENY_CHALLENGE);
	}
	
	private function cleanTransactionValue($transactionReference){
		list($tr) = explode("-", $transactionReference);
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
	
	/**
	 * 
	 * @param string $operationType
	 * @param float|null $amount
	 * @param string|null $operationId
	 * @return \HiPay\Fullservice\Gateway\Model\Operation
	 */
	protected function _requestOperation($operationType,$amount=null,$operationId=null){
		
		$transactionReference = $this->cleanTransactionValue($this->_getPayment()->getCcTransId());
		if(is_null($operationId)){			
			$operationId = $this->_order->getIncrementId() ."-" . $operationType ."-manual";
		}
		
		$opModel = $this->_gateway->requestMaintenanceOperation($operationType, $transactionReference, $amount,$operationId);
		return$opModel;
	}

	
	
}
