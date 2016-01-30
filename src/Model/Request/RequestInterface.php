<?php

namespace Hipay\FullserviceMagento\Model\Request;

interface RequestInterface {
	
	/**
	 * Return sdk request object
	 * @see \Hipay\Fullservice\Request\AbstractRequest
	 */
	public function getRequestObject();
}