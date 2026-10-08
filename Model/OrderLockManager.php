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

namespace HiPay\FullserviceMagento\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface as HipayOrder;

class OrderLockManager
{
    /**
     * @var \HiPay\FullserviceMagento\Model\HipaySalesOrderFactory
     */
    private $hipaySalesOrderFactory;

    /**
     * @param \HiPay\FullserviceMagento\Model\HipaySalesOrderFactory $hipaySalesOrderFactory
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\HipaySalesOrderFactory $hipaySalesOrderFactory
    ) {
        $this->hipaySalesOrderFactory = $hipaySalesOrderFactory;
    }

    /**
     * Lock order for processing
     *
     * @param HipayOrder $order
     * @return bool
     * @throws LocalizedException
     */
    public function lockOrder(HipayOrder $order)
    {
        try {
            $orderId = $order->getId();
            if (!$orderId) {
                throw new LocalizedException(__('Invalid order ID'));
            }

            $hipaySalesOrder = $this->hipaySalesOrderFactory->create()->load($orderId, 'order_id');

            if ($hipaySalesOrder->getId()) {
                $hipaySalesOrder->setIsLocked(1)
                    ->setLockedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->save();
            } else {
                $hipaySalesOrder->setOrderId($orderId)
                    ->setIsLocked(1)
                    ->setLockedAt(new \DateTime())
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->save();
            }

            return true;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Could not lock order %1. Error: %2', $order->getId(), $e->getMessage())
            );
        }
    }

    /**
     * Unlock order after processing
     *
     * @param HipayOrder $order
     * @return bool
     */
    public function unlockOrder(HipayOrder $order)
    {
        try {
            $orderId = $order->getId();
            if (!$orderId) {
                return false;
            }

            $hipaySalesOrder = $this->hipaySalesOrderFactory->create()->load($orderId, 'order_id');
            if ($hipaySalesOrder->getId()) {
                $hipaySalesOrder->setIsLocked(0)
                    ->setLockedAt(null)
                    ->setUpdatedAt(new \DateTime())
                    ->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if order is locked
     *
     * @param HipayOrder $order
     * @return bool
     */
    public function isLocked(HipayOrder $order)
    {
        $orderId = $order->getId();
        if (!$orderId) {
            return false;
        }

        $hipaySalesOrder = $this->hipaySalesOrderFactory->create()->load($orderId, 'order_id');
        return (bool)$hipaySalesOrder->getIsLocked();
    }
}
