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
namespace HiPay\FullserviceMagento\Model\System\Config\Source\SplitPayment;

use Magento\Framework\Data\OptionSourceInterface;
use HiPay\FullserviceMagento\Model\SplitPayment;

/**
 * Class Options
 */
class Status implements OptionSourceInterface
{


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = array('label'=>__('Pending'),'value'=>SplitPayment::SPLIT_PAYMENT_STATUS_PENDING);
        $options[] = array('label'=>__('Complete'),'value'=>SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE);
        $options[] = array('label'=>__('Failed'),'value'=>SplitPayment::SPLIT_PAYMENT_STATUS_FAILED);
        
        return $options;
    }
}
