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

use HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest;

/**
 * Billing info Request Object
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class BillingInfo extends AbstractInfoRequest
{
    /**
     * @var string
     */
    protected const KEY_FIRSTNAME = 'firstname';

    /**
     * @var string
     */
    protected const KEY_LASTNAME = 'lastname';

    /**
     * {@inheritDoc}
     *
     * @return CustomerBillingInfoRequest
     * @see    \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $billingAddress = $this->_order->getBillingAddress();
        $customerBillingInfo = new CustomerBillingInfoRequest();

        // Using guest email address if billing info is not set
        $additionalInformation = $this->_order->getPayment()->getAdditionalInformation();
        if (!empty($additionalData['guestEmail'])) {
            $customerBillingInfo->email = $additionalInformation['guestEmail'];
        }

        if (!empty($billingAddress)) {
            if ($customerEmail = $billingAddress->getEmail()) {
                $customerBillingInfo->email = $this->_order->getCustomerEmail();
            }
            $dob = $this->_order->getCustomerDob();
            if ($dob !== null && !empty($dob)) {
                try {
                    $dob = new \DateTime($dob);
                    $customerBillingInfo->birthdate = $dob->format('Ymd');
                } catch (Exception $e) {
                    $customerBillingInfo->birthdate = null;
                }
            }

            $customerBillingInfo->gender = $this->getHipayGender($this->_order->getCustomerGender());
            $this->mapCardHolder($customerBillingInfo, $billingAddress);
            $customerBillingInfo->streetaddress = $billingAddress->getStreetLine(1);
            $customerBillingInfo->streetaddress2 = $billingAddress->getStreetLine(2);
            $customerBillingInfo->city = $billingAddress->getCity();
            $customerBillingInfo->zipcode = $billingAddress->getPostcode();
            $customerBillingInfo->country = $billingAddress->getCountryId();
            $customerBillingInfo->phone = $billingAddress->getTelephone();
            $customerBillingInfo->msisdn = $billingAddress->getTelephone();
            $customerBillingInfo->state = $billingAddress->getRegion();
            $customerBillingInfo->recipientinfo = $billingAddress->getCompany();
        }

        return $customerBillingInfo;
    }

    /**
     *  AMEX needs similar cardholder between tokenization and transaction
     *
     * @param $customerBillingInfo
     * @param BillingInfo $billingAddress
     */
    private function mapCardHolder(&$customerBillingInfo, $billingAddress)
    {
        $ccType = $this->_order->getPayment()->getCcType();
        $cardOwner = $this->_order->getPayment()->getCcOwner();
        $partsCardOwner = explode(' ', trim($cardOwner ?: ''));

        $firstName = $billingAddress->getFirstname();
        $lastName = $billingAddress->getLastname();
        $theoricCardHolder = $firstName . ' ' . $lastName;
        if (
            $cardOwner
            && count($partsCardOwner) > 1
            && ( $ccType == 'AE' || $ccType == 'american-express')
            && (self::stripAccents($theoricCardHolder) != self::stripAccents($cardOwner))
        ) {
            $firstName = $this->extractPartOfCardHolder($cardOwner, self::KEY_FIRSTNAME);
            $lastName = $this->extractPartOfCardHolder($cardOwner, self::KEY_LASTNAME);
        }

        $customerBillingInfo->firstname = $firstName;
        $customerBillingInfo->lastname = $lastName;
    }

    /**
     * Extract FirstName et LastName from cardOwner
     *
     * @param  string $cardOwner
     * @param  string $key
     * @return string
     */
    private function extractPartOfCardHolder($cardOwner, $key)
    {
        $split = explode(' ', trim($cardOwner ?: ''));
        switch ($key) {
            case 'firstname':
                return $split[0];
            case 'lastname':
                return trim(preg_replace('/' . $split[0] . '/', "", $cardOwner ?: '', 1));
            default:
                return "";
        }
    }

    /**
     *
     * @param  $str
     * @return string
     */
    private static function stripAccents($str)
    {
        return strtr(
            mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1'),
            mb_convert_encoding('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'UTF-8', 'ISO-8859-1'),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }
}
