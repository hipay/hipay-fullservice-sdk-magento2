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

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Hipay;
use Hipay\Fullservice\Gateway\Model\Collection\PaymentProductCollection;


/**
 *
 * @author kassim
 *        
 */
class Config implements ConfigInterface {
	
	/**
	 * Payment actions
	 */
	const PAYMENT_ACTION_SALE = 'Sale';
	
	const PAYMENT_ACTION_AUTH = 'Authorization';
	

	/**
	 * Current payment method code
	 *
	 * @var string
	 */
	protected $_methodCode;
	
	/**
	 * Current store id
	 *
	 * @var int
	 */
	protected $_storeId;
	
	/**
	 * @var MethodInterface
	 */
	protected $methodInstance;
	
	/**
	 * @var string
	 */
	protected $pathPattern;
	
	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			 $params = []
			) {
				$this->_scopeConfig = $scopeConfig;
				
				if ($params) {
					$method = array_shift($params);
					$this->setMethod($method);
					if ($params) {
						$storeId = array_shift($params);
						$this->setStoreId($storeId);
					}
				}
	}
	
	/**
	 * Sets method instance used for retrieving method specific data
	 *
	 * @param MethodInterface $method
	 * @return $this
	 */
	public function setMethodInstance($method)
	{
		$this->methodInstance = $method;
		return $this;
	}
	
	 /**
     * Returns payment configuration value
     *
     * @param string $key
     * @param null $storeId
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($key, $storeId = null)
    {
        switch ($key) {
            case 'getDebugReplacePrivateDataKeys':
                return $this->methodInstance->getDebugReplacePrivateDataKeys();
            default:
                $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
                $path = $this->_getSpecificConfigPath($underscored);
                if ($path !== null) {
                    $value = $this->_scopeConfig->getValue(
                        $path,
                        ScopeInterface::SCOPE_STORE,
                        $this->_storeId
                    );
                    $value = $this->_prepareValue($underscored, $value);
                    return $value;
                }
        }
        return null;
    }
    
    /**
     * Method code setter
     *
     * @param string|MethodInterface $method
     * @return $this
     */
    public function setMethod($method)
    {
    	if ($method instanceof MethodInterface) {
    		$this->_methodCode = $method->getCode();
    	} elseif (is_string($method)) {
    		$this->_methodCode = $method;
    	}
    	return $this;
    }
    
 /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->_methodCode = $methodCode;
    }
    
    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
    	return $this->_methodCode;
    }
    

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
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
				\Hipay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE => __('Stage'),
				\Hipay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION => __('Production'),
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
	
	public function getEnv(){
		return $this->getValue('env');
	}
	
	public function isStageMode(){
		return $this->getEnv() == \Hipay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
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
	
	public function getGeneraleValue($key){

		return  $this->_scopeConfig->getValue(
				$this->_mapGeneralFieldset($key),
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$this->_storeId
				);
	}
	
	/**
	 * Store ID setter
	 *
	 * @param int $storeId
	 * @return $this
	 */
	public function setStoreId($storeId)
	{
		$this->_storeId = (int)$storeId;
		return $this;
	}
	
	/**
	 * Map any supported payment method into a config path by specified field name
	 *
	 * @param string $fieldName
	 * @return string|null
	 */
	protected function _getSpecificConfigPath($fieldName)
	{
		if ($this->pathPattern) {
			return sprintf($this->pathPattern, $this->_methodCode, $fieldName);
		}
	
		return "payment/{$this->_methodCode}/{$fieldName}";
	}
	
	/**
	 * Perform additional config value preparation and return new value if needed
	 *
	 * @param string $key Underscored key
	 * @param string $value Old value
	 * @return string Modified value or old value
	 */
	protected function _prepareValue($key, $value)
	{
		return $value;
	}
	
	 /**
     * Map HiPay General Settings
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapGeneralFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'api_username':
            case 'api_password':
            case 'secret_passphrase':
            case 'api_username_test':
            case 'api_password_test':
            case 'secret_passphrase_test':
            	return "hipay/hipay_credentials/{$fieldName}";
            default:
                return null;
        }
    }
}