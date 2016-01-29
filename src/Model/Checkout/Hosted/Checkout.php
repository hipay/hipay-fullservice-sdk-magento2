<?php

/*
 * Hipay fullservice SDK
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
namespace Hipay\FullserviceMagento\Model\Checkout\Hosted;

use Hipay\FullserviceMagento\Model\Checkout\AbstractCheckout;
use Magento\Customer\Model\AccountManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Hipay\Fullservice\Gateway\Client\GatewayClient;
use Hipay\Fullservice\HTTP\GuzzleClient;
use Hipay\Fullservice\HTTP\Configuration\Configuration;

/**
 * @author kassim
 *
 */
class Checkout extends AbstractCheckout{

	
	/**
	 * Reserve order ID for specified quote and start checkout on Hipay hosted
	 *
	 * @return string
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function start()
	{
		$this->_quote->collectTotals();
	
		if (!$this->_quote->getGrandTotal()) {
			throw new \Magento\Framework\Exception\LocalizedException(
					__(
							'Hipay fullservice can\'t process orders with a zero balance due. '
							. 'To finish your purchase, please go through the standard checkout process.'
							)
					);
		}
	
		$this->_quote->reserveOrderId();
		$this->quoteRepository->save($this->_quote);

		$configuration = new Configuration($this->_config->getApiUsername(), $this->_config->getApiPassword(),$this->_config->getValue('env'));
		$clientProvider = new GuzzleClient($configuration);
		$gateway = new GatewayClient($clientProvider);
		
		$hppModel = $gateway->requestHostedPaymentPage($this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\HostedPaymentPage'));
		return $hppModel->getForwardUrl();
	}
}






