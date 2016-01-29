<?php
/*
 * Hipay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace Hipay\FullserviceMagento\Model\Request\Customer;

use Hipay\FullserviceMagento\Model\Request\AbstractRequest;
use Hipay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest;

/**
 * Shipping info Request Object
 * 
 * @package Hipay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - Hipay
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ShippingInfo extends AbstractRequest {
	
	
	public function getSdkRequestObject(){
		
		$customerShippingInfo = new CustomerShippingInfoRequest();
		
		$customer = $this->_customerSession->getCustomerData();

		$customerShippingInfo->shipto_firstname = $customer->getFirstname();
		$customerShippingInfo->shipto_lastname = $customer->getLastname();

		
		$shippingAddress = $this->_quote->getShippingAddress();
		$customerShippingInfo->shipto_streetaddress = $shippingAddress->getStreetLine(1);
		$customerShippingInfo->shipto_streetaddress2 = $shippingAddress->getStreetLine(2);
		$customerShippingInfo->shipto_city = $shippingAddress->getCity();
		$customerShippingInfo->shipto_zipcode = $shippingAddress->getPostcode();
		$customerShippingInfo->shipto_country = $shippingAddress->getCountryId();
		$customerShippingInfo->shipto_phone = $shippingAddress->getTelephone();
		$customerShippingInfo->shipto_state = $shippingAddress->getRegion();
		$customerShippingInfo->shipto_recipientinfo = $shippingAddress->getCompany();
		
		return $customerShippingInfo;
		
	}
	
}