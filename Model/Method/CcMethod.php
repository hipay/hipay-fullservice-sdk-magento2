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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
use HiPay\FullserviceMagento\Model\Card;

/**
 * Class API PaymentMethod
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CcMethod extends FullserviceMethod
{
    public const HIPAY_METHOD_CODE = 'hipay_cc';

    /**
     * @var string
     */
    protected $_formBlockType = \HiPay\FullserviceMagento\Block\Cc\Form::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \HiPay\FullserviceMagento\Block\Cc\Info::class;

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Assign data to info model instance
     *
     * @param  \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {

        parent::assignData($data);

        $additionalData = $data;
        if ($data->hasData(PaymentInterface::KEY_ADDITIONAL_DATA)) {
            $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
            if (!is_object($additionalData)) {
                $additionalData = new DataObject($additionalData ?: []);
            }
        }

        $this->debugData($additionalData->debug());
        $info = $this->getInfoInstance();
        $info->setCcType($additionalData->getCcType())
            ->setCcOwner($additionalData->getCcOwner())
            ->setCcLast4(substr($additionalData->getCcNumber() ?? '', -4))
            ->setCcNumber($additionalData->getCcNumber())
            ->setCcCid($additionalData->getCcCid())
            ->setCcExpMonth($additionalData->getCcExpMonth())
            ->setCcExpYear($additionalData->getCcExpYear())
            ->setCcSsIssue($additionalData->getCcSsIssue())
            ->setCcSsStartMonth($additionalData->getCcSsStartMonth())
            ->setCcSsStartYear($additionalData->getCcSsStartYear());

        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param  string                        $paymentAction
     * @param  \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

        $cardMultiUse = (bool) $payment->getAdditionalInformation('card_multi_use');

        if ($cardMultiUse) {
            $cardData = $payment->getAdditionalInformation();
            $customerId = $order->getCustomerId();

            if ($cardData && $customerId) {
                $this->saveCard($cardData, $customerId);
            }
        }

        $this->processAction($paymentAction, $payment);
        $stateObject->setIsNotified(false);
    }

    /**
     * Perform actions based on passed action name
     *
     * @param  string                              $action
     * @param  Magento\Payment\Model\InfoInterface $payment
     * @return void
     */
    protected function processAction($action, $payment)
    {
        $totalDue = $payment->getOrder()->getTotalDue();
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();

        switch ($action) {
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_AUTH:
                $this->authorize($payment, $baseTotalDue);
                // base amount will be set inside
                $payment->setAmountAuthorized($totalDue);
                break;
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_SALE:
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $this->capture($payment, $payment->getOrder()->getBaseGrandTotal());
                break;
            default:
                break;
        }
    }

    /**
     * Authorize payment abstract method
     *
     * @param                                         \Magento\Payment\Model\InfoInterface $payment
     * @param                                         float                                $amount
     * @return                                        $this
     * @throws                                        LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);
        $this->place($payment);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return                                       $this
     * @throws                                       \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        /*
         * calling parent validate function
         */
        parent::validate();

        $info = $this->getInfoInstance();

        if (!$info->getCcType()) {
            return $this;
        }

        if ($info->getAdditionalInformation('card_token')) {
            return $this;
        }

        $errorMsg = false;
        $availableTypes = explode(',', $this->getConfigData('cctypes') ?: '');

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (in_array($info->getCcType(), $availableTypes)) {
            // Other credit card type number validation
            if ($this->validateCcNum($ccNumber)
                || (
                    $this->otherCcType($info->getCcType())
                    && $this->validateCcNumOther($ccNumber)
                )
            ) {
                $ccTypeRegExpList = [
                    //Solo, Switch or Maestro. International safe
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)' .
                        '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)' .
                        '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))' .
                        '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))' .
                        '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                    // Visa
                    'VI' => '/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC' => '/^5[1-5][0-9]{14}$/',
                    // American Express
                    'AE' => '/^3[47][0-9]{13}$/',
                    // Discover
                    'DI' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})' .
                        '|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
                        '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
                        '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
                        '|5[0-9]{14}))$/',
                    // JCB
                    'JCB' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}' .
                        '|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
                        '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
                        '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
                        '|5[0-9]{14}))$/',
                ];

                $ccNumAndTypeMatches = isset($ccTypeRegExpList[$info->getCcType()])
                    && preg_match($ccTypeRegExpList[$info->getCcType()], $ccNumber);
                $ccType = $ccNumAndTypeMatches ? $info->getCcType() : 'OT';

                if (!$ccNumAndTypeMatches && !$this->otherCcType($info->getCcType())) {
                    $errorMsg = __('The credit card number doesn\'t match the credit card type.');
                }
            } else {
                $errorMsg = __('Invalid Credit Card Number');
            }
        } else {
            $errorMsg = __('This credit card type is not allowed for this payment method.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = __('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = __('Please enter a valid credit card expiration date.');
        }

        if ($errorMsg) {
            throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
        }

        return $this;
    }

    /**
     * Check if CVV verification is enabled via the 'useccv' configuration field.
     *
     * @return bool
     * @api
     */
    public function hasVerification()
    {
        $configData = $this->getConfigData('useccv');
        if ($configData === null) {
            return true;
        }
        return (bool)$configData;
    }

    /**
     * Return a list of regular expressions used to validate CVV formats by card type.
     *
     * @return array
     * @api
     */
    public function getVerificationRegEx()
    {
        $verificationExpList = [
            'VI' => '/^[0-9]{3}$/',
            'MC' => '/^[0-9]{3}$/',
            'AE' => '/^[0-9]{4}$/',
            'DI' => '/^[0-9]{3}$/',
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/',
            'SO' => '/^[0-9]{3,4}$/',
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3,4}$/',
        ];
        return $verificationExpList;
    }

    /**
     * Validate that the provided expiration date is not in the past.
     *
     * @param  string $expYear
     * @param  string $expMonth
     * @return bool
     */
    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = new \DateTime();
        if (!$expYear
            || !$expMonth
            || (int)$date->format('Y') > $expYear
            || (
                (int)$date->format('Y') == $expYear
                && (int)$date->format('m') > $expMonth
            )
        ) {
            return false;
        }
        return true;
    }

    /**
     * Return true if the given card type equals 'OT' (Other).
     *
     * @param  string $type
     * @return bool
     * @api
     */
    public function otherCcType($type)
    {
        return $type == 'OT';
    }

    /**
     * Validate credit card number
     *
     * @param  string $ccNumber
     * @return bool
     * @api
     */
    public function validateCcNum($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;

        $cardNumberLength = strlen($cardNumber);

        for ($i = 0; $i < $cardNumberLength; $i++) {
            $currentNum = substr($cardNumber, $i, 1);

            /**
             * Double every second digit
             */
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }

            /**
             * Add digits of 2-digit numbers together
             */
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }

            $numSum += $currentNum;
        }

        /**
         * If the total has no remainder it's OK
         */
        return $numSum % 10 == 0;
    }

    /**
     * Other credit cart type number validation
     *
     * @param  string $ccNumber
     * @return bool
     * @api
     */
    public function validateCcNumOther($ccNumber)
    {
        return preg_match('/^\\d+$/', $ccNumber);
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getConfigData('cctypes', $quote ? $quote->getStoreId() : null) && parent::isAvailable($quote);
    }

    /**
     * Is active
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->getConfigData('active', $storeId) && $this->_hipayConfig->hasCredentials(true);
    }
}
