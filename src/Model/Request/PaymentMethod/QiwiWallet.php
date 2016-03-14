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
namespace HiPay\FullserviceMagento\Model\Request\PaymentMethod;


use HiPay\Fullservice\Gateway\Request\PaymentMethod\QiwiWalletPaymentMethod;

class QiwiWallet extends AbstractPaymentMethod{


	protected function mapRequest() {
	
		$qiwiWalletPaymentMethod = new QiwiWalletPaymentMethod();
		$qiwiWalletPaymentMethod->qiwiuser = $this->_order->getPayment()->getAdditionalInformation('qiwiuser');
		
		return $qiwiWalletPaymentMethod;
	}
}