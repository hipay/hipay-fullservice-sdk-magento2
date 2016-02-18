<?php
/*
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Model\Request\Info;

use HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest;
use HiPay\FullserviceMagento\Model\Request\Order;

/**
 * Shipping info Request Object
 * 
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ShippingInfo extends Order {
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
	 * @return \HiPay\FullserviceMagento\Model\Request\Info\ShippingInfo
	 */
	protected function mapRequest() {
		$customerShippingInfo = new CustomerShippingInfoRequest();

		
		$customerShippingInfo->shipto_firstname = $this->_order->getCustomerFirstname();
		$customerShippingInfo->shipto_lastname = $this->_order->getCustomerLastname();
		
		
		$shippingAddress = $this->_order->getShippingAddress();
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