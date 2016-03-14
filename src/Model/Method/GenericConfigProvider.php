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


class GenericConfigProvider implements ConfigProviderInterface {

	
	
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;

	
	/**
	 */
	public function __construct(
			\Magento\Framework\Url $urlBuilder
			) {

			$this->urlBuilder = $urlBuilder;

	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		return  [
		'payment'=>[
			'hiPayFullservice'=>[
				'afterPlaceOrderUrl'=>$this->urlBuilder->getUrl('hipay/payment/afterPlaceOrder',['_secure' => true])
        		],
			],
		] ;

	}

}