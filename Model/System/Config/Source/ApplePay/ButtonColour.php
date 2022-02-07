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
 * Source model for available 3ds values
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ButtonColour implements \Magento\Framework\Option\ArrayInterface
{
    protected const BLACK = 'black';
    protected const WHITE = 'white';
    protected const WHITE_WITH_LINE = 'white-with-line';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BLACK, 'label' => __('Black')],
            ['value' => self::WHITE, 'label' => __('White')],
            ['value' => self::WHITE_WITH_LINE, 'label' => __('White with line')],
        ];
    }
}
