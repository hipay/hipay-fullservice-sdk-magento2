<?php

namespace HiPay\FullserviceMagento\Model\System\Config\Source;


class CcType extends \Magento\Payment\Model\Source\Cctype
{
	/**
	 * Fullservice config model
	 *
	 * @var \HiPay\FullserviceMagento\Model\Config
	 */
	protected $_fullserviceConfig;
	
	/**
	 * Config
	 *
	 * @param \Magento\Payment\Model\Config $paymentConfig
	 */
	public function __construct(
			\Magento\Payment\Model\Config $paymentConfig,
			\HiPay\FullserviceMagento\Model\Config $fullserviceConfig
			)
	{
		$this->_paymentConfig = $paymentConfig;
		$this->_fullserviceConfig = $fullserviceConfig;
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
    	//populate options with allowed natives cc types
    	foreach ($this->_fullserviceConfig->getPaymentProducts() as $code => $name) {
    		if (in_array($code, $allowed) || !count($allowed)) {
    			$options[] = ['value' => $code, 'label' => $name];
    		}
    	}
    	 
    
    	return $options;
    }
}
