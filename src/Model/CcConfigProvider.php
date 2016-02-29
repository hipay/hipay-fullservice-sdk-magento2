<?php
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
			\Magento\Framework\Url $urlBuilder,
			array $methodCodes = []
			) {
				/*$this->ccConfig = $ccConfig;
				foreach ($methodCodes as $code) {
					$this->methods[$code] = $paymentHelper->getMethodInstance($code);
				}*/
				parent::__construct($ccConfig, $paymentHelper);
			$this->urlBuilder = $urlBuilder;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		$config =  parent::getConfig();
		$config['payment']['hipayCc'] =[
                		'tokenizeUrl'=>$this->urlBuilder->getUrl('hipay/cc/tokenize',['_secure' => true]),
						'afterPlaceOrderUrl'=>$this->urlBuilder->getUrl('hipay/cc/afterPlaceOrder',['_secure' => true]),
        ];
		
		return $config;
	}
}