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

namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for Hipay carriers
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ShippingMethodsHipay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = \HiPay\Fullservice\Data\DeliveryMethod\Collection::getItems();
        $options = [];
        foreach ($collection as $deliveryMethod) {
            $options[] = array(
                'value' => $deliveryMethod->getCode(),
                'label' => $deliveryMethod->getMode() . ' - ' . $deliveryMethod->getShipping()
            );
        }
        return $options;
    }

    /**
     *  Provide a Dele
     *
     * @param  $code
     * @return \HiPay\Fullservice\Data\DeliveryMethod|null Delivery Method if found otherwise null
     */
    public function getDeliveryMethodByCode($code)
    {
        if (!empty($code)) {
            $collection = \HiPay\Fullservice\Data\DeliveryMethod\Collection::getItems();
            foreach ($collection as $deliveryMethod) {
                if ($deliveryMethod->getCode() == $code) {
                    return $deliveryMethod;
                }
            }
        }
        return null;
    }
}
