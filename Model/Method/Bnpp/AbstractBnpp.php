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
namespace HiPay\FullserviceMagento\Model\Method\Bnpp;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Magento\Framework\Exception\LocalizedException;

class AbstractBnpp extends AbstractMethodAPI
{

    /**
     *  Additional datas
     *
     * @var array
     */
    protected $_additionalInformationKeys = ['cc_type'];

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject $additionalData
     * @return $this
     * @throws LocalizedException
     */
    public function _assignAdditionalInformation(\Magento\Framework\DataObject $additionalData)
    {
        parent::_assignAdditionalInformation($additionalData);
        $info = $this->getInfoInstance();
        $info->setCcType($additionalData->getCcType());

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        /*
         * calling parent validate function
         */
        parent::validate();
        $paymentInfo = $this->getInfoInstance();

        if (!$paymentInfo->getCcType()) {
            return $this;
        }

        $order = $paymentInfo->getQuote();
        if ($paymentInfo->getOrder()) {
            $order = $paymentInfo->getOrder();
        }

        $phoneExceptionMessage = 'The format of the phone number must match a French phone.';
        $country = 'FR';
        $billingAddress = $order->getBillingAddress();
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
