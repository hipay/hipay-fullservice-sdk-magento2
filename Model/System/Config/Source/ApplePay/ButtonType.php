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

namespace HiPay\FullserviceMagento\Model\System\Config\Source\ApplePay;

/**
 * Source model for available 3ds values
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ButtonType implements \Magento\Framework\Option\ArrayInterface
{
    const PLAIN = 'plain';
    const BUY = 'buy';
    const SET_UP = 'set-up';
    const DONATE = 'donate';
    const CHECK_OUT = 'check-out';
    const BOOK = 'book';
    const SUBSCRIBE = 'subscribe';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PLAIN, 'label' => __('Plain')],
            ['value' => self::BUY, 'label' => __('Buy')],
            ['value' => self::SET_UP, 'label' => __('Set up')],
            ['value' => self::DONATE, 'label' => __('Donate')],
            ['value' => self::CHECK_OUT, 'label' => __('Check out')],
            ['value' => self::BOOK, 'label' => __('Book')],
            ['value' => self::SUBSCRIBE, 'label' => __('Subscribe')],
        ];
    }
}
