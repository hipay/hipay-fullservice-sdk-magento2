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
namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;

class GenericConfigProvider implements ConfigProviderInterface {

	/**
	 * @var CcConfig
	 */
	protected $ccConfig;
	
	/**
	 * @var MethodInterface[]
	 */
	protected $methods = [];
	
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;

	
	/**
	 */
	public function __construct(
			CcConfig $ccConfig,
			PaymentHelper $paymentHelper,
			\Magento\Framework\Url $urlBuilder,
			array $methodCodes = []
			) {
		
			$this->ccConfig = $ccConfig;
			foreach ($methodCodes as $code) {
				$this->methods[$code] = $paymentHelper->getMethodInstance($code);
			}
			$this->urlBuilder = $urlBuilder;

	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		
		$config = [];
		foreach ($this->methods as $methodCode => $method) {
			if ($method->isAvailable()) {
				$config = array_merge_recursive($config, [
						'payment' => [
								'hiPayFullservice' => [
										'afterPlaceOrderUrl' => [$methodCode => $this->urlBuilder->getUrl('hipay/payment/afterPlaceOrder',['_secure' => true])],
										'isIframeMode' => [$methodCode => $this->isIframeMode($methodCode)]
								]
						]
				]);
			}
		}
		
		return $config;

	}
	
	protected function isIframeMode($methodCode){
		
		return (bool) $this->methods[$methodCode]->getConfigData('iframe_mode');
		
	}

}