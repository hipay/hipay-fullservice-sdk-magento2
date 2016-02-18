<?php
namespace HiPay\FullserviceMagento\Model;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Helper\Data as PaymentHelper;


class CcConfigProvider implements CcGenericConfigProvider {

	/**
	 * @var string[]
	 */
	protected $methodCodes = [
			CcMethod::HIPAY_METHOD_CODE,
	];
	
	/**
	 * @param CcConfig $ccConfig
	 * @param PaymentHelper $paymentHelper
	 * @param array $methodCodes
	 */
	public function __construct(
			CcConfig $ccConfig,
			PaymentHelper $paymentHelper,
			array $methodCodes = []
			) {
				$this->ccConfig = $ccConfig;
				foreach ($methodCodes as $code) {
					$this->methods[$code] = $paymentHelper->getMethodInstance($code);
				}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		return parent::getConfig();
	}
}