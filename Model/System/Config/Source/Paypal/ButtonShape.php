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

namespace HiPay\FullserviceMagento\Model\System\Config\Source\Paypal;

/**
 * Source model for Button Shape
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class ButtonShape implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Button Colors
     */
    public const BUTTON_PILL = 'pill';
    public const BUTTON_RECT = 'rect';

    /**
     * Return button shape options as value-label pairs for configuration
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            self::BUTTON_PILL => __('Rounded'),
            self::BUTTON_RECT => __('Rectangle')
        ];
    }
}
