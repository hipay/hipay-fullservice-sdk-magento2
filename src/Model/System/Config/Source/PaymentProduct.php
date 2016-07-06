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
namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for available payment products
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaymentProduct extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
	const PAYMENT_PRODUCT_FIELD = 'payment_products_categories';

	
	/**
	 * Payment config model
	 *
	 * @var \Magento\Payment\Model\Config
	 */
	protected $_paymentConfig;
	
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
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			)
	{
		$this->_paymentConfig = $paymentConfig;
		$this->_scopeConfig = $scopeConfig;
	}
	
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {

    	$categories = null;
    	
    	if($this->getPath()){
    		
	    	list($section_locale,$method) = explode("/", $this->getPath());
	    	list($section) = explode("_",$section_locale);
	    	
	    	$categories = $this->_scopeConfig->getValue(implode('/',[$section,$method,self::PAYMENT_PRODUCT_FIELD])) ?: null;

	    	if(!empty($categories)){
	    		$categories = explode(',',$categories);
	    	}
    	}
    	
    	$list = [];
    	foreach($this->getPaymentProducts($categories) as $paymentProduct){
    		$list[] = ['value'=>$paymentProduct->getProductCode(),'label'=>$paymentProduct->getBrandName()];
    	}
    	
    	return $list;

    }
    
    /**
     * Payment products source getter
     *
     * @return \HiPay\Fullservice\Data\PaymentProduct[]
     */
    public function getPaymentProducts($categories = null){
    	/* @var $collection \HiPay\Fullservice\Data\PaymentProduct[] */
    	$collection = \HiPay\Fullservice\Data\PaymentProduct\Collection::getItems($categories);
    	 
    	return $collection;
    }
}
