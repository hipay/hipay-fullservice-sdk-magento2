<?php

/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */

namespace HiPay\FullserviceMagento\Model\System\Config\Source\Status;

/**
 * Capture Order Statuses source model
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class StatusValidate
{
    /**
     * Return capture status options as value-label pairs for configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'value' => 117,
            'label' => __('Capture Requested')
        ];

        $options[] = [
            'value' => 118,
            'label' => __('Capture')
        ];

        return $options;
    }
}
