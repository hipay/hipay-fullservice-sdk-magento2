<?php
/**
 * HiPay Fullservice Magento
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
use Magento\Framework\View\Asset\Source;

/**
 * Class CC config provider
 * Can bu used by all Cc API payment method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
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
	 * @var CcConfig
	 */
	protected $ccConfig;
	
	
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
	 * @var \Magento\Framework\View\Asset\Source
	 */
	protected $assetSource;
	
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
			\HiPay\FullserviceMagento\Model\Config\Factory $configFactory,
			Source $assetSource
			) {
			$this->method = $paymentHelper->getMethodInstance($this->methodCode);
			$this->urlBuilder = $urlBuilder;
			$this->_cctypeSource = $cctypeSource;
			$this->ccConfig = $ccConfig;
			$this->assetSource = $assetSource;
			
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
				'apiPasswordTokenJs'=>$this->_hipayConfig->getApiPasswordTokenJs(),
				'icons' => $this->getIcons()
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
		$types = $this->_cctypeSource->toKeyValue();
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
	
	/**
	 * Get icons for available payment methods
	 *
	 * @return array
	 */
	protected function getIcons()
	{
		$icons = [];
		$types = $this->getCcAvailableTypesOrdered();
		foreach (array_keys($types) as $code) {
			if (!array_key_exists($code, $icons)) {
				$asset = $this->ccConfig->createAsset('HiPay_FullserviceMagento::images/cc/' . strtolower($code) . '.png');
				$placeholder = $this->assetSource->findRelativeSourceFilePath($asset);
				if ($placeholder) {
					list($width, $height) = getimagesize($asset->getSourceFile());
					$icons[$code] = [
							'url' => $asset->getUrl(),
							'width' => $width,
							'height' => $height
					];
				}
			}
		}
		return $icons;
	}

}