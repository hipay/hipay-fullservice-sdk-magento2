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

use HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest;

/**
 * Shipping info Request Object
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ShippingInfo extends AbstractInfoRequest
{
    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     * @return \HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo
     */
    protected function mapRequest()
    {
        $customerShippingInfo = new CustomerShippingInfoRequest();
        if ($this->_order->getIsVirtual()) {
            return $customerShippingInfo;
        }

        $shippingAddress = $this->_order->getShippingAddress();
        $customerShippingInfo->shipto_firstname = $shippingAddress->getFirstname();
        $customerShippingInfo->shipto_lastname = $shippingAddress->getLastname();
        $customerShippingInfo->shipto_streetaddress = $shippingAddress->getStreetLine(1);
        $customerShippingInfo->shipto_streetaddress2 = $shippingAddress->getStreetLine(2);
        $customerShippingInfo->shipto_city = $shippingAddress->getCity();
        $customerShippingInfo->shipto_zipcode = $shippingAddress->getPostcode();
        $customerShippingInfo->shipto_country = $shippingAddress->getCountryId();
        $customerShippingInfo->shipto_phone = $shippingAddress->getTelephone();
        $customerShippingInfo->shipto_state = $shippingAddress->getRegion();
        $customerShippingInfo->shipto_recipientinfo = $shippingAddress->getCompany();
        $customerShippingInfo->shipto_msisdn = $shippingAddress->getTelephone();
        $customerShippingInfo->shipto_gender = $this->getHipayGender($this->_order->getCustomerGender());

        return $customerShippingInfo;
    }
}
