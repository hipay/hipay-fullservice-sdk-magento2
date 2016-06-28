<?php
/**
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
namespace HiPay\FullserviceMagento\Model\System\Config\Source\Status;

/**
 * Order Statuses to capture order
 */
class StatusValidate 
{
    public function toOptionArray() {
        $options = array();
		
		$options[] = array(
            'value' => 117,
            'label' => __('Capture Requested')
        );
		
		$options[] = array(
            'value' => 118,
            'label' => __('Capture')
        );
       
        return $options;
    }
}
