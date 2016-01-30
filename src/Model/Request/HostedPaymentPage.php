<?php

namespace Hipay\FullserviceMagento\Model\Request;


use Hipay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
/**
 * @author kassim
 *
 */
class HostedPaymentPage extends Order{

	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Hipay\FullserviceMagento\Model\Request\Order::getRequestObject()
	 * @return \Hipay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
	 */
	protected function mapRequest(){
		
		$hppRequest = new HostedPaymentPageRequest();
		$orderRequest = parent::mapRequest();
		
		foreach (get_object_vars($orderRequest) as $property=>$value) {
			$hppRequest->$property = $value;
		}
		
		$hppRequest->css = $this->_config->getValue('css_url');
		$hppRequest->template = $this->_config->getValue('template');
		
		$hppRequest->payment_product_list = implode(",",$this->_config->getPaymentProductsList());

		$hppRequest->payment_product_category_list = implode(",", $this->_config->getPaymentProductCategoryList());
		
		
		return $hppRequest;
		
	}


}