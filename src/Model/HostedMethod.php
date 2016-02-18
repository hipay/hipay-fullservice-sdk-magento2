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
namespace HiPay\FullserviceMagento\Model;

use Magento\Payment\Model\Method\Online\GatewayInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\ConfigInterface;


/**
 * Class PaymentMethod
 * @package HiPay\FullserviceMagento\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedMethod extends FullserviceMethod implements GatewayInterface {
	
	const HIPAY_METHOD_CODE               = 'hipay_hosted';
	
	/**
	 * @var string
	 */
	protected $_formBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Form';
	
	/**
	 * @var string
	 */
	protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Info';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Magento\Payment\Model\Method\Online\GatewayInterface::postRequest()
	 */
	public function postRequest(DataObject $request, ConfigInterface $config) {
		$this->logger->debug("Post request called");
		$this->logger->debug(print_r($request->toArray(),true));
		$this->logger->debug(print_r($config,true));
		die('foo');
	}
	

	
}