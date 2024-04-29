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

namespace HiPay\FullserviceMagento\Model\System\Config\Source\PaypalV2;

/**
 * Source model for Button Color
 *
 * @author    HiPay
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class ButtonColor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Button Colors
     */
    public const BUTTON_GOLD = 'gold';
    public const BUTTON_BLUE = 'blue';
    public const BUTTON_BLACK = 'black';
    public const BUTTON_SILVER = 'silver';
    public const BUTTON_WHITE = 'white';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        // TODO: Implement toOptionArray() method.
        $button = [
            self::BUTTON_GOLD => __('Gold'),
            self::BUTTON_BLUE => __('Blue'),
            self::BUTTON_BLACK => __('Black'),
            self::BUTTON_SILVER => __('Silver'),
            self::BUTTON_WHITE => __('White')
        ];

        return $button;
    }
}
