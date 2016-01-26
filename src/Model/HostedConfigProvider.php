<?php
namespace Hipay\FSM2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class HostedConfigProvider implements ConfigProviderInterface {
	
	/**
	 * @var string[]
	 */
	protected $methodCode = \Hipay\FSM2\Model\HostedMethod::HIPAY_HOSTED_METHOD_CODE;
	
	/**
	 * @var Checkmo
	 */
	protected $method;
	
	/**
	 * @var Escaper
	 */
	protected $escaper;
	
	/**
	 * @param PaymentHelper $paymentHelper
	 * @param Escaper $escaper
	 */
	public function __construct(
			PaymentHelper $paymentHelper,
			Escaper $escaper
			) {
				$this->escaper = $escaper;
				$this->method = $paymentHelper->getMethodInstance($this->methodCode);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Magento\Checkout\Model\ConfigProviderInterface::getConfig()
	 */
	public function getConfig() {
		 return $this->method->isAvailable() ? [
            'payment' => [
                'checkmo' => [],
            ],
        ] : [];
	}

}