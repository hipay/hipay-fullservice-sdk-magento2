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
 * Source model for available 3ds values
 */
class ThreeDs implements \Magento\Framework\Option\ArrayInterface
{

/**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>__('Disabled')),
            array('value' => 1, 'label'=>__('Try to enable for all transactions.')),
            array('value' => 2, 'label'=>__('Try to enable for configured 3ds rules')),
        	array('value' => 3, 'label'=>__('Force for configured 3ds rules')),
        	array('value' => 4, 'label'=>__('Force for all transactions.')),
            
        );
    }
	
}
