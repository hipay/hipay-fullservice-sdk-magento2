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
namespace HiPay\FullserviceMagento\Model;

use Magento\Payment\Model\CcConfig;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Checkout\Model\ConfigProviderInterface;


class CcConfigProvider implements ConfigProviderInterface {

	
	/**
	 * @var string
	 */
	protected $methodCode = CcMethod::HIPAY_METHOD_CODE;
	
	/**
	 * @var CcMethod
	 */
	protected $method;
	
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\System\Config\Source\CcType $_cctypes
	 */
	protected $_cctypeSource;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
	 */
	protected $_hipayConfig;
	
	/**
	 * @param CcConfig $ccConfig
	 * @param PaymentHelper $paymentHelper
	 * @param \Magento\Framework\Url $urlBuilder
	 * @param \HiPay\FullserviceMagento\Model\System\Config\Source\CcType $cctypeSource
	 * @param \HiPay\FullserviceMagento\Model\Config\Factory $configFactory
	 */
	public function __construct(
			CcConfig $ccConfig,
			PaymentHelper $paymentHelper,
			\Magento\Framework\Url $urlBuilder,
			\HiPay\FullserviceMagento\Model\System\Config\Source\CcType $cctypeSource,
			\HiPay\FullserviceMagento\Model\Config\Factory $configFactory
			) {
			$this->method = $paymentHelper->getMethodInstance($this->methodCode);
			$this->urlBuilder = $urlBuilder;
			$this->_cctypeSource = $cctypeSource;
			
			$this->_hipayConfig = $configFactory->create(['params'=>['methodCode'=>$this->methodCode]]);
			
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		return $this->method->isAvailable() ? [
		'payment'=>[
			'hipayCc'=>[
				'availableTypes'=>$this->getCcAvailableTypesOrdered(),
				'env'=>$this->_hipayConfig->getApiEnv(),
				'apiUsername'=>$this->_hipayConfig->getApiUsername(),
				'apiPassword'=>$this->_hipayConfig->getApiPassword()
        		],
			],
		] : [] ;

	}
	
	/**
	 * Retrieve availables credit card types and preserve saved order
	 *
	 * @param string $methodCode
	 * @return array
	 */
	protected function getCcAvailableTypesOrdered()
	{
		$types = $this->_cctypeSource->toOptionArray();
		$availableTypes = $this->method->getConfigData('cctypes');
		if(!is_array($availableTypes)){
			$availableTypes = explode(",", $availableTypes);
		}
		$ordered = [];
		foreach($availableTypes as $key) {
			if(array_key_exists($key,$types)) {
				$ordered[$key] = $types[$key]['label'];
			}
		}
		
		return $ordered;
	}

}