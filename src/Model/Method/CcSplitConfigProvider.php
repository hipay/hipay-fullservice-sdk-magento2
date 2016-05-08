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

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use HiPay\FullserviceMagento\Model\Method\CcSplitMethod;
use Magento\Payment\Model\MethodInterface;

class CcSplitConfigProvider implements ConfigProviderInterface {

	
	/**
	 * @var string $methodCode
	 */
	protected $methodCode = CcSplitMethod::HIPAY_METHOD_CODE;
	
	/**
	 * @var MethodInterface[]
	 */
	protected $methods = [];
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory
	 */
	protected $ppCollectionFactory;
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\Collection $paymentProfiles
	 */
	protected $paymentProfiles;


	
	/**
	 * @param PaymentHelper $paymentHelper
	 */
	public function __construct(
			PaymentHelper $paymentHelper,
			\HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory,
			array $methodCodes = []
			) {
		
			foreach ($methodCodes as $code) {
				$this->methods[$code] = $paymentHelper->getMethodInstance($code);
			}
			$this->ppCollectionFactory = $ppCollectionFactory;
			
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
								'hipayCcSplit' => [
										'paymentProfiles' => [$methodCode => $this->getPaymentProfiles($methodCode)]
								]
						]
				]);
			}
		}
		
		
		
		return $config;

	}
	
	/**
	 * 
	 * @param string $methodCode
	 * @return []
	 */
	protected function getPaymentProfiles($methodCode){

			
		$ppIds = $this->methods[$methodCode]->getConfigData('split_payments');
		if(!is_array($ppIds)){
			$ppIds = explode(',',$ppIds);
		}
		$this->paymentProfiles = $this->ppCollectionFactory->create();
		$this->paymentProfiles->addFieldToFilter('profile_id',array('IN'=>$ppIds));
		
		$pProfiles = [];
		
		foreach ($this->paymentProfiles as $pp){
			$pProfiles[] = [
					'name'=>$pp->getName(),
					'profileId'=>$pp->getProfileId()
					
			];
		}

		
		return $pProfiles;
		
		
	}
	
}