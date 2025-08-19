<?php

namespace HiPay\FullserviceMagento\Api;

interface ResponseNotFoundOrderRepositoryInterface
{
    /**
     * Save a HiPay order ID into pending orders
     */
    public function savePendingOrder(string $orderId): void;

    /**
     * Delete pending order by HiPay order ID
     */
    public function deletePendingOrder(string $orderId): void;

    /**
     * Check if pending order exists
     */
    public function isPendingOrderExist(string $orderId): bool;
}
