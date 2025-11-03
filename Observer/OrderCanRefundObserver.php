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

namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 * HiPay module observer
 *
 * Check if order can be refund with HiPay Fullservice Payment Method
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class OrderCanRefundObserver implements ObserverInterface
{
    /**
     * Add accept and capture buuton to order view toolbar
     *
     * @param  EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
        */
        $order = $observer->getOrder();
        if ($order->getStatus() == Config::STATUS_CAPTURE_REQUESTED) {
            $order->setForcedCanCreditmemo(false);
        }

        if ($order->getPayment()
            && strpos($order->getPayment()->getMethod(), 'hipay') !== false && $order->hasInvoices()
        ) {
            //If configuration validate order with status 117
            // (capture requested) and Notification 118 (Captured) is not received
            // we disallow refund
            if ((int)$order->getPayment()->getMethodInstance()->getConfigData('hipay_status_validate_order')
                == TransactionStatus::CAPTURE_REQUESTED
            ) {
                $savedStatues = $order->getPayment()->getAdditionalInformation('saved_statues');
                if (!is_array($savedStatues) || !isset($savedStatues[TransactionStatus::CAPTURED])) {
                    $order->setForcedCanCreditmemo(false);
                }
            }
        }

        return $this;
    }
}
