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
 * MB Way Hosted Fields Model payment method
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class MbwayHostedFields extends LocalHostedFields
{
    public const HIPAY_METHOD_CODE = 'hipay_mbway_hosted_fields';

    /**
     * @var string
     */
    protected static $_technicalCode = 'mbway';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * @var string[] keys to import in payment additionnal informations
     */
    protected $_additionalInformationKeys = ['phone', 'browser_info', 'cc_type'];

    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();

        $order = $info->getQuote();
        if ($info->getOrder()) {
            $order = $info->getOrder();
        }

        $phoneExceptionMessage = 'The format of the phone number must match a Portuguese phone.';
        $country = 'PT';
        $localizedException = new LocalizedException(__($phoneExceptionMessage));

        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse($order->getPayment()->getAdditionalInformation('phone'), $country);

            if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw $localizedException;
            }

            $order->getPayment()->setAdditionalInformation(
                'phone',
                str_replace(
                    ' ',
                    '',
                    $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL)
                )
            );
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
