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
				'apiUsernameTokenJs'=>$this->_hipayConfig->getApiUsernameTokenJs(),
				'apiPasswordTokenJs'=>$this->_hipayConfig->getApiPasswordTokenJs()
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