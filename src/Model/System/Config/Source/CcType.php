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
	 * Config
	 *
	 * @param \Magento\Payment\Model\Config $paymentConfig
	 */
	public function __construct(
			\Magento\Payment\Model\Config $paymentConfig,
			\HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct $paymentProductSource
			)
	{
		$this->_paymentConfig = $paymentConfig;
		$this->_paymentProductSource = $paymentProductSource;
	}
	
    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE','SM','cb','bcmc'];
        //FAIRE que du payment product en configuration, puis surchargé la config getCcType pour retourner le code Magento ou le cas échant le code Hipay
        //Comme cela magento gère avec les cctypes et l'API Hipay gère directement ses propres code payment products
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
    			$options[] = ['value' => $code, 'label' => $name];
    		}
    	}
    	
    	//populate options with allowed fullservice payment methods
    	foreach ($this->_paymentProductSource->toOptionArray() as $option) {
    		if (in_array($option['value'], $allowed) || !count($allowed)) {
    			$options[] = $option;
    		}
    	}
    	 
    
    	return $options;
    }
}
