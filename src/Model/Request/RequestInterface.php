<?php

namespace HiPay\FullserviceMagento\Model\Request;

interface RequestInterface {
	
	/**
	 * Return sdk request object
	 * @see \HiPay\Fullservice\Request\AbstractRequest
	 */
	public function getRequestObject();
}