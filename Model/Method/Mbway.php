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
use \Magento\Framework\Exception\LocalizedException;

/**
 * MB Way payment method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Mbway extends AbstractMethodAPI
{
    const HIPAY_METHOD_CODE = 'hipay_mbway';

    /**
     * @var string
     */
    protected static $_technicalCode = 'mbway';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

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
        $billingAddress = $order->getBillingAddress();

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
