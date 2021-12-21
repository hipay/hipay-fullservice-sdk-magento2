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
     * @var int 48H
     */
    public $overridePendingTimeout = 2880;

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

        $phoneExceptionMessage = 'The format of the phone number must match %s phone.';
        $billingAddress = $order->getBillingAddress();
        $country = $billingAddress->getCountryId();

        switch ($country) {
            case 'FR':
                $phoneExceptionMessage = sprintf($phoneExceptionMessage, 'a French');
                break;
            case 'IT':
                $phoneExceptionMessage = sprintf($phoneExceptionMessage, 'an Italian');
                break;
            case 'BE':
                $phoneExceptionMessage = sprintf($phoneExceptionMessage, 'a Belgian');
                break;
        }

        $localizedException = new LocalizedException(__($phoneExceptionMessage));
        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse($billingAddress->getTelephone(), $country);

            if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw $localizedException;
            }

            $billingAddress->setTelephone($phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164));
        } catch (NumberParseException $e) {
            $this->_logger->critical($e);
            throw $localizedException;
        } catch (Exception $e) {
            $this->_logger->critical($e);
            throw $localizedException;
        }

        return $this;
    }
}
