<?php

namespace HiPay\FullserviceMagento\Model\Request;


use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
/**
 * @author kassim
 *
 */
class HostedPaymentPage extends Order{

	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \HiPay\FullserviceMagento\Model\Request\Order::getRequestObject()
	 * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
	 */
	protected function mapRequest(){
		
		$hppRequest = new HostedPaymentPageRequest();
		$orderRequest = parent::mapRequest();
		
		foreach (get_object_vars($orderRequest) as $property=>$value) {
			$hppRequest->$property = $value;
		}
		//Inherit from parent class Order but no used in this pbject request
		unset($hppRequest->payment_product);
		
		$hppRequest->css = $this->_config->getValue('css_url');
		$hppRequest->template = ((bool)$this->_config->getValue('iframe_mode') && !$this->_config->isAdminArea()) ? 'iframe' : $this->_config->getValue('template');
		
		$hppRequest->payment_product_list = implode(",",$this->_config->getPaymentProductsList());

		$hppRequest->payment_product_category_list = implode(",", $this->_config->getPaymentProductCategoryList());
		
		return $hppRequest;
		
	}


}