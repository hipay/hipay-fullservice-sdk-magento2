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
 * Source model for available payment actions
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
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
