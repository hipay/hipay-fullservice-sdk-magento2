<?php
/*
 * HiPay fullservice SDK
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
namespace Hipay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\FullserviceMethod;

class QiwiWallet extends FullserviceMethod{
	
	const HIPAY_METHOD_CODE               = 'hipay_qiwiwallet';
	
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
	 * @var string[] keys to import in payment additionnal informations
	 */
	protected $_additionalInformationKeys = ['qiwiuser'];
	
	
	
}