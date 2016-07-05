<?php

namespace HiPay\FullserviceMagento\Model\Request\SecureVault;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use HiPay\Fullservice\SecureVault\Request\GenerateTokenRequest;


/**
 * @author kassim
 *
 */
class GenerateToken extends BaseRequest{
	
	/**
	 * 
	 * @var \Magento\Sales\Model\Order\Payment $_payment
	 */
	protected $_payment;

	/**
	 * {@inheritDoc}
	 * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
	 */
	public function __construct(
			\Psr\Log\LoggerInterface $logger,
			\Magento\Checkout\Helper\Data $checkoutData,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\Locale\ResolverInterface $localeResolver,
			\HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
			\Magento\Framework\Url $urlBuilder,
			\HiPay\FullserviceMagento\Helper\Data $helper,
			$params = []
			)
	{
		
		parent::__construct($logger, $checkoutData, $customerSession, $checkoutSession, $localeResolver, $requestFactory, $urlBuilder,$helper,$params);
		
		
		if (isset($params['payment']) && $params['payment'] instanceof \Magento\Sales\Model\Order\Payment) {
			$this->_payment = $params['payment'];
		} else {
			throw new \Exception('Payment instance is required.');
		}
		
	}

	
	/**
	 * @return \HiPay\Fullservice\SecureVault\Request\GenerateTokenRequest
	 */
	protected function mapRequest(){
		
		
		$generateRequest = new GenerateTokenRequest();
		$generateRequest->card_number = $this->_payment;
		
		return $generateRequest;
	}

}