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
namespace HiPay\FullserviceMagento\Model\Config;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Store\Model\ScopeInterface;


/**
 *
 * @author kassim
 *        
 */
abstract class AbstractConfig implements ConfigInterface {
	
	
	

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
	 * Core store config
	 *
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfig;
	

	
	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			) {
				$this->_scopeConfig = $scopeConfig;
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
	
	public function getGeneraleValue($key,$group = 'hipay_credentials'){

		return  $this->_scopeConfig->getValue(
				$this->_mapGeneralFieldset($key,$group),
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
    protected function _mapGeneralFieldset($fieldName,$group = 'hipay_credentials')
    {
        switch ($fieldName) {
            case 'api_username':
            case 'api_password':
            case 'secret_passphrase':
            case 'api_username_test':
            case 'api_password_test':
            case 'secret_passphrase_test':
            	return "hipay/{$group}/{$fieldName}";
            default:
                return null;
        }
    }
}