<?php
/*
 * HiPay fullservice SDK
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
namespace Hipay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\HostedMethod;

class Sisal extends HostedMethod{
	
	const HIPAY_METHOD_CODE               = 'hipay_sisal';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefund = false;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefundInvoicePartial = false;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canUseInternal = false;
	
	
	
}