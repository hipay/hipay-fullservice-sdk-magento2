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

use function Symfony\Component\String\s;

/**
 * Source model for Button Label
 *
 * @author    HiPay
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class ButtonLabel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Button Labels
     */
    public const BUTTON_PAYPAL = 'paypal';
    public const BUTTON_PAY = 'pay';
    public const BUTTON_SUBSCRIBE = 'subscribe';
    public const BUTTON_CHECKOUT = 'checkout';
    public const BUTTON_BUYNOW = 'buynow';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        // TODO: Implement toOptionArray() method.
        $button = [
            self::BUTTON_PAYPAL => __('Paypal'),
            self::BUTTON_PAY => __('Pay'),
            self::BUTTON_SUBSCRIBE => __('Subscribe'),
            self::BUTTON_CHECKOUT => __('Checkout'),
            self::BUTTON_BUYNOW => __('Buy Now')
        ];

        return $button;
    }
}
