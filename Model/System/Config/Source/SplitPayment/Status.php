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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\System\Config\Source\SplitPayment;

use Magento\Framework\Data\OptionSourceInterface;
use HiPay\FullserviceMagento\Model\SplitPayment;

/**
 * Class Split payment status Options
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
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
        $options[] = array('label' => __('Pending'), 'value' => SplitPayment::SPLIT_PAYMENT_STATUS_PENDING);
        $options[] = array('label' => __('Complete'), 'value' => SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE);
        $options[] = array('label' => __('Failed'), 'value' => SplitPayment::SPLIT_PAYMENT_STATUS_FAILED);

        return $options;
    }
}
