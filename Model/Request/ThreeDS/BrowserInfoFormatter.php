<?php

/**
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2019 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\ThreeDS;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo;

/**
 *
 * @author    HiPay <support@hipay.com>
 * @copyright Copyright (c) 2019 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class BrowserInfoFormatter extends AbstractRequest
{
    /**
     * @var \HiPay\FullserviceMagento\Helper\ThreeDSTwo
     */
    protected $_threeDSHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * BrowserInfoFormatter constructor.
     *
     * @param  \Psr\Log\LoggerInterface                             $logger
     * @param  \Magento\Checkout\Helper\Data                        $checkoutData
     * @param  \Magento\Customer\Model\Session                      $customerSession
     * @param  \Magento\Checkout\Model\Session                      $checkoutSession
     * @param  \Magento\Framework\Locale\ResolverInterface          $localeResolver
     * @param  \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory
     * @param  \Magento\Framework\UrlInterface                      $urlBuilder
     * @param  \HiPay\FullserviceMagento\Helper\Data                $helper
     * @param  \HiPay\FullserviceMagento\Helper\ThreeDSTwo          $threeDSHelper
     * @param  array                                                $params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper,
        $params = []
    ) {
        parent::__construct(
            $logger,
            $checkoutData,
            $customerSession,
            $checkoutSession,
            $localeResolver,
            $requestFactory,
            $urlBuilder,
            $helper,
            $params
        );

        $this->_threeDSHelper = $threeDSHelper;
        $this->_order = $params["order"];
    }

    /**
     * {@inheritDoc}
     *
     * @return BrowserInfo
     * @see    \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $browserInfo = new BrowserInfo();

        $browserData = json_decode($this->_order->getPayment()->getAdditionalInformation('browser_info'));

        $browserInfo->ipaddr = $this->_order->getRemoteIp();
        $browserInfo->http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
        $browserInfo->javascript_enabled = true;

        if ($browserData !== null) {
            $browserInfo->java_enabled = isset($browserData->java_enabled) ? $browserData->java_enabled : null;
            $browserInfo->language = isset($browserData->language) ? $browserData->language : null;
            $browserInfo->color_depth = isset($browserData->color_depth) ? $browserData->color_depth : null;
            $browserInfo->screen_height = isset($browserData->screen_height) ? $browserData->screen_height : null;
            $browserInfo->screen_width = isset($browserData->screen_width) ? $browserData->screen_width : null;
            $browserInfo->timezone = isset($browserData->timezone) ? $browserData->timezone : null;
            $browserInfo->http_user_agent = isset($browserData->http_user_agent) ? $browserData->http_user_agent : null;
        }

        return $browserInfo;
    }
}
