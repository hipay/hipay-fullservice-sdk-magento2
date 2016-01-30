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
		$orderRequest = parent::getRequestObject();
		
		foreach (get_object_vars($orderRequest) as $property=>$value) {
			$hppRequest->$property = $value;
		}
		
		return $hppRequest;
		
	}

}