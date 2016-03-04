<?php

namespace HiPay\FullserviceMagento\Model\System\Config\Source;


class CcType extends \Magento\Payment\Model\Source\Cctype
{
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
	}
	
    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE','SM','cb','bcmc'];
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

    	$configData =$this->_scopeConfig->getValue('payment/hipay_cc/cctypes');
    	$availableTypes = explode(",", $configData);
    		
    	$ordered = array();
    	foreach($availableTypes as $key) {
    		if(array_key_exists($key,$options)) {
    			$ordered[$key] = $options[$key];
    			unset($options[$key]);
    		}
    	}
    		
    	return array_merge($ordered,$options);
    	
    }
    
}
