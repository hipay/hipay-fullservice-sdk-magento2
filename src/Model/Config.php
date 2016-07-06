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
namespace HiPay\FullserviceMagento\Model;


use HiPay\Fullservice\HTTP\Configuration\Configuration as ConfigSDK;
use HiPay\FullserviceMagento\Model\Config\AbstractConfig;
use HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use HiPay\FullserviceMagento\Model\System\Config\Source\Environments;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions;
use HiPay\FullserviceMagento\Model\System\Config\Source\Templates;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct;


/**
 * Main Config Class
 * Retrieve general configuration and sources
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Config extends AbstractConfig implements ConfigurationInterface {
	
	const STATUS_AUTHORIZED = 'hipay_authorized';
	const STATUS_AUTHORIZATION_REQUESTED = 'hipay_authorization_requested';
	const STATUS_AUTHORIZED_PENDING = "hipay_authorized_pending";
	const STATUS_CAPTURE_REQUESTED = 'hipay_capture_requested';
	const STATUS_PARTIALLY_CAPTURED = 'hipay_partially_captured';
	const STATUS_REFUND_REQUESTED = 'hipay_refund_requested';
	const STATUS_REFUND_REFUSED = 'hipay_refund_refused';
	const STATUS_PARTIALLY_REFUNDED = 'hipay_partially_refunded';
	const STATUS_EXPIRED = 'hipay_expired';
	const STATUS_AUTHENTICATION_REQUESTED = 'hipay_authentication_requested';
	
	/**
	 * 
	 * @var ConfigurationInterface $_configSDK
	 */
	protected $_configSDK;
	
	/**
	 * 
	 * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
	 */
	protected $_storeManager;
	
	/**
	 * 
	 * @var \Magento\Framework\App\State $appState
	 */
	protected $appState;
	
	/**
	 * @var \Psr\Log\LoggerInterface $logger
	 */
	protected $logger;
	
	/**
	 * Order Needed for some configurations (Eg. MO/TO credentials ...)
	 *
	 * @var \Magento\Sales\Model\Order $_order
	 */
	protected $_order;
	
	/**
	 * 
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\App\State $appState
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param array $params
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\Magento\Framework\App\State $appState,
			\Psr\Log\LoggerInterface $logger,
			 $params = []
			) {
				parent::__construct($scopeConfig);
				$this->_storeManager = $storeManager;
				$this->appState = $appState;
				$this->logger = $logger;
				
				if ($params && isset($params['methodCode'])) {
					$method = $params['methodCode'];
					$this->setMethod($method);
					if (isset($params['storeId'])) {
						$storeId = $params['storeId'];
						$this->setStoreId($storeId);
					}
					
					if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
						$this->setOrder($params['order']);
					} 
					
					
				}
				
				
				//Default credentials
				$apiUsername = $this->getApiUsername();
				$apiPassword =  $this->getApiPassword();
				
				//If is Admin store, we use MO/TO credentials
				if($this->mustUseMotoCredentials()){
					$apiUsername = $this->getApiUsernameMoto();
					$apiPassword = $this->getApiPasswordMoto();
					
				}
				
				//@TODO Find a better way for verification of api username and api password
				//@TODO Maybe create a new Config Object with arg order required, for check MO/TO action
				try {
					
					$this->_configSDK = new ConfigSDK($apiUsername, $apiPassword,$this->getApiEnv(),'application/json');
					
				} catch (\Exception $e) {
					$this->logger->critical($e->getMessage());
					$this->_configSDK = null;
				}
				
	}
	
	/**
	 * Check if we must to use MO/TO credentials
	 * Essentialy, Admin operations
	 * @return bool
	 */
	public function mustUseMotoCredentials(){
		
		$hasOrder = !is_null($this->getOrder());
		$hasLastTransId = false;
		$isMoto = false;
		
		if($hasOrder){
			$hasLastTransId = $this->getOrder()->getPayment()->getLastTransId() ? true : false;
			$isMoto = (bool)$this->getOrder()->getPayment()->getAdditionalInformation('is_moto') ?: false;
			
		}
		
		return $this->isAdminArea() && $hasOrder 
						&& (!$hasLastTransId || ($hasLastTransId && $isMoto));
		
	}
    
	/**
	 * Return if current store is admin
	 * @return bool
	 */
	public function isAdminArea(){
		return $this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
	}
    /**
     * Templates type source getter
     *
     * @return array
     */
    public function getTemplates()
    {
    	return (new Templates())->getTemplates();

    }
    
    /**
     * 
     */
    public function getPaymentProductsList(){
    	
    	$list = explode(",",$this->getValue('payment_products'));
    	return $list;
    }
    
    public function getPaymentProductCategoryList(){
    	return $this->getAllowedPaymentProductCategories();
    }
    
    public function getPaymentProductsToOptionArray(){
    	$list = [];
    	foreach($this->getPaymentProducts() as $paymentProduct){
    		$list[] = ['value'=>$paymentProduct->getProductCode(),'label'=>$paymentProduct->getBrandName()];
    	}
    	return $list;
    }
	
    /**
     * Payment products source getter
     *
     * @return array
     */
    public function getPaymentProducts(){
    	$pp = (new PaymentProduct())->getPaymentProducts($this->getAllowedPaymentProductCategories());
    	return $pp;
    	
    }
    
    public function getAllowedPaymentProductCategories(){
    	return explode(",",$this->getValue('payment_products_categories'));
    }
    
	/**
	 * Payment actions source getter
	 *
	 * @return array
	 */
	public function getPaymentActions()
	{

		return (new PaymentActions())->getPaymentActions();
	}
	
	/**
	 * Environments source getter
	 *
	 * @return array
	 */
	public function getEnvironments()
	{
	
		return (new Environments())->getEnvironments();
	}

	
	public function isStageMode(){
		return $this->getApiEnv() == ConfigSDK::API_ENV_STAGE;
	}
	
	public function hasCredentials($withTokenJs = false){
		
		if($withTokenJs){
				
			//token JS credential
			$apiUsernameTokenJs = $this->getApiUsernameTokenJs();
			$apiPasswordTokenJs = $this->getApiPasswordTokenJs();
		
			if(empty($apiUsernameTokenJs) || empty($apiPasswordTokenJs)){
				return false;
			}
		
		}
		
		//default api username, password, secret passphrase
		$apiUsername = $this->getApiUsername();
		$apiPassword = $this->getApiPassword();
		$secretKey = $this->getSecretPassphrase();
		
		
		//check if is admin are and change values if needed
		if($this->mustUseMotoCredentials()){
			$apiUsername = $this->getApiUsernameMoto();
			$apiPassword = $this->getApiPasswordMoto();
			$secretKey = $this->getSecretPassphraseMoto();
		}
		
		//return false if one of them if empty
		if(empty($apiUsername) || empty($apiPassword) || empty($secretKey)){
			return false;
		}
		
		 return true;
	}
	
	public function getApiUsername(){
		$key = "api_username";
		if($this->isStageMode()){
			$key = "api_username_test";
		}
		
		return  $this->getGeneraleValue($key);
	}
	
	public function getApiPassword(){
		$key = "api_password";
		if($this->isStageMode()){
			$key = "api_password_test";
		}
	
		return  $this->getGeneraleValue($key);
	}
	
	public function getSecretPassphrase(){
		$key = "secret_passphrase";
		if($this->isStageMode()){
			$key = "secret_passphrase_test";
		}
		
		return  $this->getGeneraleValue($key);
	}
	
	public function getApiUsernameMoto(){
		$key = "api_username";
		if($this->isStageMode()){
			$key = "api_username_test";
		}
	
		return  $this->getGeneraleValue($key,'hipay_credentials_moto');
	}
	
	public function getApiPasswordMoto(){
		$key = "api_password";
		if($this->isStageMode()){
			$key = "api_password_test";
		}
	
		return  $this->getGeneraleValue($key,'hipay_credentials_moto');
	}
	
	public function getSecretPassphraseMoto(){
		$key = "secret_passphrase";
		if($this->isStageMode()){
			$key = "secret_passphrase_test";
		}
	
		return  $this->getGeneraleValue($key,'hipay_credentials_moto');
	}
	
	public function getApiUsernameTokenJs(){
		$key = "api_username";
		if($this->isStageMode()){
			$key = "api_username_test";
		}
	
		return  $this->getGeneraleValue($key,'hipay_credentials_tokenjs');
	}
	
	public function getApiPasswordTokenJs(){
		$key = "api_password";
		if($this->isStageMode()){
			$key = "api_password_test";
		}
	
		return  $this->getGeneraleValue($key,'hipay_credentials_tokenjs');
	}
	
	
	
	public function getApiEndpoint(){
		return !is_null($this->_configSDK) ? $this->_configSDK->getApiEndpoint() : '';
	}
	
	public function getApiEndpointProd(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getApiEndpointProd() : '';
	}
	
	public function getApiEndpointStage(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getApiEndpointStage() : '';
	}
	
	public function getSecureVaultEndpointProd(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getSecureVaultEndpointProd() : '';
	}
	
	public function getSecureVaultEndpointStage(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getSecureVaultEndpointStage() : '';
	}
	
	public function getSecureVaultEndpoint(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getSecureVaultEndpoint() : '';
	}
	
	public function getApiEnv(){
		return $this->getValue('env');
	}
	
	public function getApiHTTPHeaderAccept(){
		return !is_null($this->_configSDK) ?  $this->_configSDK->getApiHTTPHeaderAccept() : '';
	}
	
	public function getOrder(){
		return $this->_order;
	}
	
	public function setOrder($order){
		$this->_order = $order;
	}

}