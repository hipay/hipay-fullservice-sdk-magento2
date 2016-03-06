<?php

namespace HiPay\FullserviceMagento\Model\System\Config\Source;


class CcType extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
	
	/**
	 * Allowed CC types
	 *
	 * @var array
	 */
	protected $_allowedTypes = [];
	
	/**
	 * Payment config model
	 *
	 * @var \Magento\Payment\Model\Config
	 */
	protected $_paymentConfig;
	
	/**
	 * Fullservice config model
	 *
	 * @var \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct
	 */
	protected $_paymentProductSource;
	
	/**
	 * Core store config
	 *
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfig;
	
	/**
	 * Config
	 *
	 * @param \Magento\Payment\Model\Config $paymentConfig
	 */
	public function __construct(
			\Magento\Payment\Model\Config $paymentConfig,
			\HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct $paymentProductSource,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			)
	{
		$this->_paymentConfig = $paymentConfig;
		$this->_paymentProductSource = $paymentProductSource;
		$this->_scopeConfig = $scopeConfig;
		
		$this->_allowedTypes = ['VI', 'MC', 'AE','SM','cb','bcmc'];
	}
	
	/**
	 * Return allowed cc types for current method
	 *
	 * @return array
	 */
	public function getAllowedTypes()
	{
		return $this->_allowedTypes;
	}
	
	/**
	 * Setter for allowed types
	 *
	 * @param array $values
	 * @return $this
	 */
	public function setAllowedTypes(array $values)
	{
		$this->_allowedTypes = $values;
		return $this;
	}
	
    
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
    	
    	/**
    	 * making filter by allowed cards
    	 */
    	$allowed = $this->getAllowedTypes();
    	$options = [];
    	
    	//populate options with allowed natives cc types
    	foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
    		if (in_array($code, $allowed) || !count($allowed)) {
    			$options[$code] = ['value' => $code, 'label' => $name];
    		}
    	}
    	
    	//populate options with allowed fullservice payment methods
    	foreach ($this->_paymentProductSource->toOptionArray() as $option) {
    		if (in_array($option['value'], $allowed) || !count($allowed)) {
    			$options[$option['value']] = $option;
    		}
    	}
    	
    	$ordered = array();
    	
		if($this->getPath()){
			list($section_locale,$method,$field) = explode("/", $this->getPath());
	    	list($section) = explode("_",$section_locale);
	    	
	    	$configData =$this->_scopeConfig->getValue(implode("/",[$section,$method,$field]));
	    	
	    	$availableTypes = explode(",", $configData);
	    		
	    	
	    	foreach($availableTypes as $key) {
	    		if(array_key_exists($key,$options)) {
	    			$ordered[$key] = $options[$key];
	    			unset($options[$key]);
	    		}
	    	}
		}
    		
    	return array_merge($ordered,$options);
    	
    }
    
}
