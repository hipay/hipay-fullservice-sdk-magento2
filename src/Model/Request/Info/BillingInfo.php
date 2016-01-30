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
namespace Hipay\FullserviceMagento\Model\Request\Info;


use Hipay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest;
use Hipay\FullserviceMagento\Model\Request\Order;

/**
 * Billing info Request Object
 * 
 * @package Hipay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - Hipay
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class BillingInfo extends Order {
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Hipay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
	 * @return \Hipay\FullserviceMagento\Model\Request\Info\BillingInfo
	 */
	protected function mapRequest() {
		$customerBillingInfo = new CustomerBillingInfoRequest();

		$customerBillingInfo->firstname = $this->_order->getCustomerFirstname();
		$customerBillingInfo->lastname = $this->_order->getCustomerLastname();
		$customerBillingInfo->email = $this->_order->getCustomerEmail();
		$dob = $this->_order->getCustomerDob();
		if(!is_null($dob)){
			try {
				
			$dob = new \DateTime($dob);
			$customerBillingInfo->birthdate = $dob->format('Ymd') ;
			} catch (Exception $e) {
				
			}
		}
		
		$customerBillingInfo->gender =$this->_order->getCustomerGender(); //@TODO make mapping Value with \Hipay\Fullservice\Enum\Customer\Gender
		
		$billingAddress = $this->_order->getBillingAddress();
		$customerBillingInfo->streetaddress = $billingAddress->getStreetLine(1);
		$customerBillingInfo->streetaddress2 = $billingAddress->getStreetLine(2);
		$customerBillingInfo->city = $billingAddress->getCity();
		$customerBillingInfo->zipcode = $billingAddress->getPostcode();
		$customerBillingInfo->country = $billingAddress->getCountryId();
		$customerBillingInfo->phone = $billingAddress->getTelephone();
		$customerBillingInfo->state = $billingAddress->getRegion();
		$customerBillingInfo->recipientinfo = $billingAddress->getCompany();
		
		return $customerBillingInfo;
	}

	
}