<?php

/**
 * HiPay fullservice SDK
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

namespace HiPay\FullserviceMagento\Block\Redirect;

class PendingPolling extends Pending
{
    private const POLLING_INTERVAL = 10000;
    private const POLLING_MAX_ATTEMPTS = 30;

    /**
     * @return string
     */
    public function getPlaceOrderStatusUrl()
    {
        return $this->getUrl('hipay/payment/pendingStatus');
    }

    /**
     * @return string
     */
    public function getPendingUrl()
    {
        return $this->getUrl('hipay/redirect/pendingpolling');
    }

    /**
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->getUrl('hipay/redirect/decline');
    }

    /**
     * @return int
     */
    public function getPollingInterval()
    {
        return self::POLLING_INTERVAL;
    }

    /**
     * @return int
     */
    public function getPollingMaxAttempts()
    {
        return self::POLLING_MAX_ATTEMPTS;
    }
}
