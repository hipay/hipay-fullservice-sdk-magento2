<?php
/**
 * HiPay Plugin
 *
 * Override "getCustomData" Method in HiPay\FullserviceMagento\Helper
 *
 * Used to customize custom data field
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

namespace HiPay\FullserviceMagento\Plugin;

use \HiPay\FullserviceMagento\Model\Request\Order as Order;

class CustomDataPlugin
{

    /**
     *  Complete general getCustomData with HiPay'datas
     *
     * @see \HiPay\FullserviceMagento\Helper\Data
     * @param \HiPay\FullserviceMagento\Model\Request\Order $subject
     * @param $result
     * @return array
     */
    public function afterGetCustomData(Order $subject, $result)
    {
        $order = $subject->getOrder();
        $payment = $order->getPayment();

        // Shipping description
        $result['shipping_description'] = $order->getShippingDescription();

        // Customer information
        if ($order->getCustomerId()) {
            $customerId = $order->getCustomerId();
            $customer = $subject->getCustomerRepositoryInterface()->getById($customerId);
            $group = $subject->getGroupRepositoryInterface()->getById($customer->getGroupId());
            $result['customer_code'] = $group->getCode();
        }

        // Method payment information
        $result['payment_code'] = $payment->getMethodInstance()->getCode();
        $result['display_iframe'] = 0;

        if ($payment->getMethodInstance()->getConfigData('iframe_mode')) {
            $result['display_iframe'] = $payment->getMethodInstance()->getConfigData('iframe_mode');
        }

        /* Wait feature
        if ($split_number) {
            $customData['payment_type'] = 'Split ' . $split_number;
        }*/

        // Use OneClick
        if ($payment->getAdditionalInformation('create_oneclick')) {
            $result['payment_type'] = 'OneClick';
        }
        return $result;
    }
}
