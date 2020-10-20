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
namespace HiPay\FullserviceMagento\Model\Method\Facilypay;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use \Magento\Framework\Exception\LocalizedException;

class AbstractFacilypay extends AbstractMethodAPI
{

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();

        $order = $info->getQuote();
        if ($info->getOrder()) {
            $order = $info->getOrder();
        }

        $phoneExceptionMessage = 'The format of the phone number must match {COUNTRY} phone.';
        $billingAddress = $order->getBillingAddress();
        $country = $billingAddress->getCountryId();

        switch ($country) {
            case 'FR':
                $phoneExceptionMessage = str_replace('{COUNTRY}', 'a French', $phoneExceptionMessage);
                break;
            case 'IT':
                $phoneExceptionMessage = str_replace('{COUNTRY}', 'an Italian', $phoneExceptionMessage);
                break;
            case 'BE':
                $phoneExceptionMessage = str_replace('{COUNTRY}', 'a Belgian', $phoneExceptionMessage);
                break;
        }

        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse($billingAddress->getTelephone(), $country);

            if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw new LocalizedException(__($phoneExceptionMessage));
            }

            $billingAddress->setTelephone($phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164));
        } catch (NumberParseException | Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(__($phoneExceptionMessage));
        }

        return $this;
    }
}
