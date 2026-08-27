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

/**
 * Request-scoped holder for the MO/TO hosted page redirect URL.
 *
 * The order-placement observer stores the HiPay hosted page URL here, and the
 * admin order-create controller plugin reads it to return the redirect. Shared
 * (singleton) within a request, so both see the same instance.
 *
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HostedMotoRedirect
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * @param  string|null $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }
}
