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
class PaymentProducts implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getPaymentProducts();
    }
    
    /**
     * Payment products source getter
     *
     * @return array
     */
    public function getPaymentProducts(){
    	/* @var $collection \HiPay\Fullservice\Gateway\Model\PaymentProduct[] */
    	$collection = \HiPay\Fullservice\Gateway\Model\Collection\PaymentProductCollection::getItems();
    	$list = [];
    	foreach($collection as $paymentProduct){
    		$list[] = ['value'=>$paymentProduct->getProductCode(),'label'=>$paymentProduct->getBrandName()];
    	}
    	 
    	return $list;
    }
}
