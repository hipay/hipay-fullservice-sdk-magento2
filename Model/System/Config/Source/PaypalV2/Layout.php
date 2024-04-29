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
 * Source model for Layout
 *
 * @author    HiPay
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Layout actions
     */
    public const LAYOUT_VERTICAL = 'vertical';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        // TODO: Implement toOptionArray() method.
        $layout = [
            self::LAYOUT_VERTICAL => __('Vertical')
        ];

        return $layout;
    }
}
