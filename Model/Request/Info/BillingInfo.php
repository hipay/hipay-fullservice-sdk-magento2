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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Model\Request\Info;


use HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest;

/**
 * Billing info Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class BillingInfo extends AbstractInfoRequest
{

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     * @return \HiPay\FullserviceMagento\Model\Request\Info\BillingInfo
     */
    protected function mapRequest()
    {
        $customerBillingInfo = new CustomerBillingInfoRequest();
        $customerBillingInfo->email = $this->_order->getCustomerEmail();
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
        $billingAddress = $this->_order->getBillingAddress();
        $customerBillingInfo->firstname = $billingAddress->getFirstname();
        $customerBillingInfo->lastname = $billingAddress->getLastname();
        $customerBillingInfo->streetaddress = $billingAddress->getStreetLine(1);
        $customerBillingInfo->streetaddress2 = $billingAddress->getStreetLine(2);
        $customerBillingInfo->city = $billingAddress->getCity();
        $customerBillingInfo->zipcode = $billingAddress->getPostcode();
        $customerBillingInfo->country = $billingAddress->getCountryId();
        $customerBillingInfo->phone = $billingAddress->getTelephone();
        $customerBillingInfo->msisdn = $billingAddress->getTelephone();
        $customerBillingInfo->state = $billingAddress->getRegion();
        $customerBillingInfo->recipientinfo = $billingAddress->getCompany();
        return $customerBillingInfo;
    }
}
