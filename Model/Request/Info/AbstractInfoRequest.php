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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\Info;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use HiPay\Fullservice\Enum\Customer\Gender as HipayGender;
use HiPay\FullserviceMagento\Model\Request\Type\Factory;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Url;
use Psr\Log\LoggerInterface;

/**
 * Abstract Info Request Object
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class AbstractInfoRequest extends BaseRequest
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @inheritDoc
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
     *
     * @param LoggerInterface $logger
     * @param Data $checkoutData
     * @param Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ResolverInterface $localeResolver
     * @param Factory $requestFactory
     * @param Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param array $params
     * @throws LocalizedException
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\Url $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        array $params = []
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

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException('Order instance is required.');
        }
    }

    /**
     * Convert Magento gender value to Hipay gender code.
     *
     * @param  int $magentoGender
     * @return string
     */
    protected function getHipayGender($magentoGender)
    {
        switch ($magentoGender) {
            case 1:
            case 'M':
                return HipayGender::MALE;
            case 'F':
            case 2:
                return HipayGender::FEMALE;
            default:
                return HipayGender::UNKNOWN;
        }
    }
}
