<?php

namespace Hipay\FullserviceMagento\Model\Request;


use Hipay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
/**
 * @author kassim
 *
 */
class HostedPaymentPage extends Order{

	/**
	 * {@inheritDoc}
	 * @see \Hipay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
	 */
	public function __construct(
			\Psr\Log\LoggerInterface $logger,
			\Magento\Customer\Model\Url $customerUrl,
			\Magento\Tax\Helper\Data $taxData,
			\Magento\Checkout\Helper\Data $checkoutData,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\Magento\Framework\UrlInterface $coreUrl,
			\Magento\Quote\Api\CartManagementInterface $quoteManagement,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
			$params = []
			)
	{
		
		parent::__construct( $logger, $customerUrl, $taxData, $checkoutData, $customerSession, $storeManager, $coreUrl, $quoteManagement, $checkoutSession, $totalsCollector, $params);
		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Hipay\FullserviceMagento\Model\Request\Order::getRequestObject()
	 * @return \Hipay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
	 */
	public function getRequestObject(){
		
		$hppRequest = new HostedPaymentPageRequest();
		$orderRequest = parent::getRequestObject();
		
		foreach (get_class_vars($orderRequest) as $property=>$value) {
			$hppRequest->$property = $value;
		}
		
		return $hppRequest;
		
	}

}