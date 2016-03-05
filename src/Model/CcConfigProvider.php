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

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Helper\Data as PaymentHelper;


class CcConfigProvider extends CcGenericConfigProvider {

	/**
	 * @var string[]
	 */
	protected $methodCodes = [
			CcMethod::HIPAY_METHOD_CODE,
	];
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;
	
	/**
	 * @param CcConfig $ccConfig
	 * @param PaymentHelper $paymentHelper
	 * @param array $methodCodes
	 */
	public function __construct(
			CcConfig $ccConfig,
			PaymentHelper $paymentHelper,
			\Magento\Framework\Url $urlBuilder
			) {
				parent::__construct($ccConfig, $paymentHelper,$this->methodCodes);
			$this->urlBuilder = $urlBuilder;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		$config = parent::getConfig();
		$config['payment']['hipayCc'] =[
                		'tokenizeUrl'=>$this->urlBuilder->getUrl('hipay/cc/tokenize',['_secure' => true]),
						'afterPlaceOrderUrl'=>$this->urlBuilder->getUrl('hipay/cc/afterPlaceOrder',['_secure' => true]),
						'availableTypes'=>$this->getCcAvailableTypesOrdered()
        ];
		
		return $config;
	}
	
	/**
	 * Retrieve availables credit card types and preserve saved order
	 *
	 * @param string $methodCode
	 * @return array
	 */
	protected function getCcAvailableTypesOrdered($methodCode = 'hipay_cc')
	{
		$types = $this->ccConfig->getCcAvailableTypes();
		$availableTypes = $this->methods[$methodCode]->getConfigData('cctypes');
		if(!is_array($availableTypes)){
			$availableTypes = explode(",", $availableTypes);
		}
		$ordered = [];
		foreach($availableTypes as $key) {
			if(array_key_exists($key,$types)) {
				$ordered[$key] = $types[$key];
			}
		}
		
		return $ordered;
	}
	
	/**
	 * Whether switch/solo card type available
	 *
	 * @param string $methodCode
	 * @return bool
	 */
	protected function hasSsCardType($methodCode)
	{
		return false;
		$result = false;
		$availableTypes = explode(',', $this->methods[$methodCode]->getConfigData('cctypes'));
		$ssPresentations = array_intersect(['SS', 'SO'], $availableTypes);
		if ($availableTypes && count($ssPresentations) > 0) {
			$result = true;
		}
		return $result;
	}
}