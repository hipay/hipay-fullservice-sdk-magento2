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
namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for available payment products
 */
class PaymentProduct implements \Magento\Framework\Option\ArrayInterface
{

	
	/**
	 * Payment config model
	 *
	 * @var \Magento\Payment\Model\Config
	 */
	protected $_paymentConfig;
	
	/**
	 * Config
	 *
	 * @param \Magento\Payment\Model\Config $paymentConfig
	 */
	public function __construct(\Magento\Payment\Model\Config $paymentConfig)
	{
		$this->_paymentConfig = $paymentConfig;
	}
	
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
    	
    	$list = [];
    	foreach($this->getPaymentProducts() as $paymentProduct){
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
