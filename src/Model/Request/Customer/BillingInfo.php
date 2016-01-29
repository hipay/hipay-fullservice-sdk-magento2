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
use Hipay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest;

/**
 * Billing info Request Object
 * 
 * @package Hipay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - Hipay
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class BillingInfo extends AbstractRequest {
	
	
	public function getSdkRequestObject(){
		
		$customerBillingInfo = new CustomerBillingInfoRequest();
		
		$customer = $this->_customerSession->getCustomerData();
		$customerBillingInfo->firstname = $customer->getFirstname();
		$customerBillingInfo->lastname = $customer->getLastname();
		$customerBillingInfo->email = $customer->getEmail();
		$dob = $customer->getDob();
		if(!is_null($dob)){
			try {
				
			$dob = new \DateTime($dob);
			$customerBillingInfo->birthdate = $dob->format('Ymd') ;
			} catch (Exception $e) {
				
			}
		}
		
		$customerBillingInfo->gender = $customer->getGender(); //@TODO make mapping Value with \Hipay\Fullservice\Enum\Customer\Gender
		
		$billingAddress = $this->_quote->getBillingAddress();
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