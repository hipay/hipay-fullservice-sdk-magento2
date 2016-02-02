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
namespace Hipay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for available payment actions
 */
class PaymentActions implements \Magento\Framework\Option\ArrayInterface
{

	/**
	 * Payment actions
	 */
	const PAYMENT_ACTION_SALE = 'Sale';
	
	const PAYMENT_ACTION_AUTH = 'Authorization';
	
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getPaymentActions();
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
}
