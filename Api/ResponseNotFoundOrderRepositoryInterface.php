<?php

namespace HiPay\FullserviceMagento\Api;

interface ResponseNotFoundOrderRepositoryInterface
{
    /**
     * Save a HiPay order ID into pending orders
     *
     * @param string $orderId
     * @return void
     */
    public function savePendingOrder(string $orderId): void;

    /**
     * Delete pending order by HiPay order ID
     *
     * @param string $orderId
     * @return void
     */
    public function deletePendingOrder(string $orderId): void;

    /**
     * Check if pending order exists
     *
     * @param string $orderId
     * @return bool
     */
    public function isPendingOrderExist(string $orderId): bool;
}
