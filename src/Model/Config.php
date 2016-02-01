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
namespace Hipay\FullserviceMagento\Model;


use Hipay\Fullservice\Gateway\Model\Collection\PaymentProductCollection;
use Hipay\Fullservice\HTTP\Configuration\Configuration as ConfigSDK;
use Hipay\FullserviceMagento\Model\Config\AbstractConfig;
use Hipay\Fullservice\HTTP\Configuration\ConfigurationInterface;


/**
 *
 * @author kassim
 *        
 */
class Config extends AbstractConfig implements ConfigurationInterface {

	
	/**
	 * 
	 * @var ConfigurationInterface $_configSDK
	 */
	protected $_configSDK;
	
	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			 $params = []
			) {
				parent::__construct($scopeConfig);
				
				if ($params) {
					$method = array_shift($params);
					$this->setMethod($method);
					if ($params) {
						$storeId = array_shift($params);
						$this->setStoreId($storeId);
					}
				}

				$this->_configSDK = new ConfigSDK($this->getApiUsername(), $this->getApiPassword(),$this->getApiEnv());
	}
    
    /**
     * Templates type source getter
     *
     * @return array
     */
    public function getTemplates()
    {
    	return [
    			\Hipay\Fullservice\Enum\Transaction\Template::BASIC_JS => __('Basic JS'),
    	];

    }
    
    /**
     * 
     */
    public function getPaymentProductsList(){
    	
    	$list = explode(",",$this->getValue('payment_products'));
    	return $list;
    }
    
    public function getPaymentProductCategoryList(){
    	//Prepare Brand Categories
    	$allPaymentProducts = PaymentProductCollection::getItems();
    	$categories = [];
    	 
    	foreach ($allPaymentProducts as $pp) {
    		if(in_array($pp->getProductCode(), $this->getPaymentProductsList()) && !in_array($pp->getCategory(),$categories)){
    			$categories[] = $pp->getCategory();
    		}
    	}
    	return $categories;
    }
	
    /**
     * Payment products source getter
     *
     * @return array
     */
    public function getPaymentProducts(){
    	/* @var $collection \Hipay\Fullservice\Gateway\Model\PaymentProduct[] */
    	$collection = \Hipay\Fullservice\Gateway\Model\Collection\PaymentProductCollection::getItems();
    	$list = [];
    	foreach($collection as $paymentProduct){
    		$list[] = ['value'=>$paymentProduct->getProductCode(),'label'=>$paymentProduct->getBrandName()];
    	}
    	
    	return $list;
    }
    
	/**
	 * Payment actions source getter
	 *
	 * @return array
	 */
	public function getPaymentActions()
	{
		$paymentActions = [
				self::PAYMENT_ACTION_AUTH => __('Authorization'),
				self::PAYMENT_ACTION_SALE => __('Sale'),
		];
	
		return $paymentActions;
	}
	
	/**
	 * Environments source getter
	 *
	 * @return array
	 */
	public function getEnvironments()
	{
		$envs = [
				ConfigSDK::API_ENV_STAGE => __('Stage'),
				ConfigSDK::API_ENV_PRODUCTION => __('Production'),
		];
	
		return $envs;
	}
	
	/**
	 * Mapper from Hipay-specific payment actions to Magento payment actions
	 *
	 * @return string|null
	 */
	public function getConfigPaymentAction()
	{
		switch ($this->getValue('paymentAction')) {
			case self::PAYMENT_ACTION_AUTH:
				return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
			case self::PAYMENT_ACTION_SALE:
				return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
		}
		return null;
	}

	
	public function isStageMode(){
		return $this->getApiEnv() == ConfigSDK::API_ENV_STAGE;
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
			$key = "secret_passphrase";
		}
		
		return  $this->getGeneraleValue($key);
	}
	
	public function getApiEndpoint(){
		return $this->_configSDK->getApiEndpoint();
	}
	
	public function getApiEndpointProd(){
		return $this->_configSDK->getApiEndpointProd();
	}
	
	public function getApiEndpointStage(){
		return $this->_configSDK->getApiEndpointStage();
	}
	
	public function getApiEnv(){
		return $this->getValue('env');
	}
	
	public function getApiHTTPHeaderAccept(){
		return $this->_configSDK->getApiHTTPHeaderAccept();
	}

}