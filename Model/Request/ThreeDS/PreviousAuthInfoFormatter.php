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
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\ThreeDS;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo;

/**
 * Account info
 *
 * @package HiPay\FullserviceMagento
 * @author HiPay <support@hipay.com>
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PreviousAuthInfoFormatter extends AbstractRequest
{
    /**
     * @var \HiPay\FullserviceMagento\Helper\ThreeDSTwo
     */
    protected $_threeDSHelper;

    /**
     * PreviousAuthInfoFormatter constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper
     * @param array $params
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
    }

    /**
     *
     * {@inheritDoc}
     *
     * @return PreviousAuthInfo
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $previousAuthInfo = new PreviousAuthInfo();

        if ($this->_threeDSHelper->isCustomerLoggedIn()) {
            $lastOrder = $this->_threeDSHelper->getCustomerLatestOrder($this->_customerId, $this->getStoreId());
            if ($lastOrder->getPayment()) {
                $previousAuthInfo->transaction_reference = $lastOrder->getPayment()->getCcTransId();
            }
        }

        return $previousAuthInfo;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->_checkoutData->getQuote()->getStoreId();
    }
}
