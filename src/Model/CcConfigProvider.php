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
			\Magento\Framework\Url $urlBuilder
			) {
				parent::__construct($ccConfig, $paymentHelper);
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
        ];
		
		return $config;
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