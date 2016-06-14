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
namespace HiPay\FullserviceMagento\Model;


use HiPay\Fullservice\HTTP\Configuration\Configuration as ConfigSDK;
use HiPay\FullserviceMagento\Model\Config\AbstractConfig;
use HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use HiPay\FullserviceMagento\Model\System\Config\Source\Environments;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions;
use HiPay\FullserviceMagento\Model\System\Config\Source\Templates;
use HiPay\Fullservice\Data\PaymentProduct\Collection;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct;


/**
 *
 * @author kassim
 *        
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
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\Magento\Framework\App\State $appState,
			 $params = []
			) {
				parent::__construct($scopeConfig);
				$this->_storeManager = $storeManager;
				$this->appState = $appState;
				
				if ($params) {
					$method = array_shift($params);
					$this->setMethod($method);
					if ($params) {
						$storeId = array_shift($params);
						$this->setStoreId($storeId);
					}
				}
				
				$apiUsername = $this->getApiUsername();
				$apiPassword =  $this->getApiPassword();
				
				//If is Admin store, we use MO/TO credentials
				if($this->isAdminArea()){
					$apiUsername = $this->getApiUsernameMoto();
					$apiPassword = $this->getApiPasswordMoto();
				}
				
				//@TODO Find a better way for verification of api username and api password
				try {					
					$this->_configSDK = new ConfigSDK($apiUsername, $apiPassword,$this->getApiEnv(),'application/json');
				} catch (\Exception $e) {
					$this->_configSDK = null;
				}
				
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
	
	public function hasCredentials(){
		
		//default api username, password, secret passphrase
		$apiUsername = $this->getApiUsername();
		$apiPassword = $this->getApiPassword();
		$secretKey = $this->getSecretPassphrase();
		
		//token JS credential
		$apiUsernameTokenJs = $this->getApiUsernameTokenJs();
		$apiPasswordTokenJs = $this->getApiPasswordTokenJs();
		
		//check if is admin are and change values if needed
		if($this->isAdminArea()){
			$apiUsername = $this->getApiUsernameMoto();
			$apiPassword = $this->getApiPasswordMoto();
			$secretKey = $this->getSecretPassphraseMoto();
		}
		
		//return false if one of them if empty
		if(empty($apiUsername) || empty($apiPassword) || empty($secretKey) 
				|| empty($apiUsernameTokenJs) || empty($apiPasswordTokenJs)){
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
		return $this->_configSDK->getApiHTTPHeaderAccept();
	}

}