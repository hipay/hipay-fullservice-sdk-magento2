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

namespace HiPay\FullserviceMagento\Model\System\Config\Source\ApplePay;

/**
 * Source model for Apple Pay multi-browser display modes
 */
class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    protected const POPUP = 'popup';
    protected const MODAL = 'modal';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::POPUP, 'label' => __('popup')],
            ['value' => self::MODAL, 'label' => __('modal')],
        ];
    }
}
