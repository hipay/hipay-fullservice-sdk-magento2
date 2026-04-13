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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Model\Method;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Magento\Framework\Exception\LocalizedException;

/**
 * Bancomat Pay Hosted Fields Model payment method
 */
class BancomatPayHostedFields extends LocalHostedFields
{
    public const HIPAY_METHOD_CODE = 'hipay_bancomatpay_hosted_fields';

    /**
     * @var string
     */
    protected static $_technicalCode = 'bancomatpay';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * @var string[] keys to import in payment additional informations
     */
    protected $_additionalInformationKeys = ['phone', 'browser_info', 'cc_type'];

    /**
     * @param string $action
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @return void
     */
    protected function processAction($action, $payment)
    {
        $totalDue = $payment->getOrder()->getTotalDue();
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();

        switch ($action) {
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_AUTH:
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_SALE:
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $this->authorize($payment, $baseTotalDue);
                break;
            default:
                break;
        }
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();
        $order = $info->getQuote();

        if ($info->getOrder()) {
            $order = $info->getOrder();
        }

        $billingAddress = $order ? $order->getBillingAddress() : null;
        $billingCountry = strtoupper((string) ($billingAddress ? $billingAddress->getCountryId() : ''));

        $localizedException = new LocalizedException(
            __('The phone number must be a valid Italian phone number.')
        );

        try {

            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse(
                (string) $order->getPayment()->getAdditionalInformation('phone'),
                'IT'
            );

            if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw $localizedException;
            }

            $order->getPayment()->setAdditionalInformation(
                'phone',
                $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164)
            );
        } catch (NumberParseException $e) {
            $this->_logger->critical($e);
            throw $localizedException;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw $localizedException;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPendingRedirectPath()
    {
        return 'hipay/redirect/pendingpolling';
    }
}
