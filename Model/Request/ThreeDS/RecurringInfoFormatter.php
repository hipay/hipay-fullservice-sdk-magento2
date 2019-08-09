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
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\RecurringInfo;
use HiPay\FullserviceMagento\Model\PaymentProfile;

/**
 *
 * @package HiPay\FullserviceMagento
 * @author HiPay <support@hipay.com>
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class RecurringInfoFormatter extends AbstractRequest
{
    protected $_threeDSHelper;

    protected $_splitProfileId;

    protected $_order;

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
        $this->_splitProfileId = $params["profile_id"];
        $this->_order = $params["order"];
    }

    /**
     * {@inheritDoc}
     *
     * @return RecurringInfo|\HiPay\Fullservice\Request\AbstractRequest
     * @throws \Magento\Framework\Exception\LocalizedException
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $recurringInfo = new RecurringInfo();

        $lastSplitPayment = $this->_threeDSHelper->getLastOrderSplitPayment($this->_order->getId());

        $recurringInfo->frequency = $this->getFrequencyDays();

        $recurringInfo->expiration_date = (int)date('Ymd', strtotime($lastSplitPayment->getDateToPay()));

        return $recurringInfo;
    }

    private function getFrequencyDays()
    {
        $paymentProfile = $this->_threeDSHelper->getPaymentProfile($this->_splitProfileId);

        $days = null;

        switch ($paymentProfile->getPeriodUnit()) {
            case PaymentProfile::PERIOD_UNIT_DAY:
                $days = 1 * (int)$paymentProfile->getPeriodFrequency();
                break;
            case PaymentProfile::PERIOD_UNIT_WEEK:
                $days = 7 * (int)$paymentProfile->getPeriodFrequency();
                break;
            case PaymentProfile::PERIOD_UNIT_SEMI_MONTH:
                $days = 14 * (int)$paymentProfile->getPeriodFrequency();
                break;
            case PaymentProfile::PERIOD_UNIT_MONTH:
                $days = 28 * (int)$paymentProfile->getPeriodFrequency();
                break;
            case PaymentProfile::PERIOD_UNIT_YEAR:
                $days = 365 * (int)$paymentProfile->getPeriodFrequency();
                break;
        }

        return ($days !== null) ? settype($days, 'string') : null;
    }
}
