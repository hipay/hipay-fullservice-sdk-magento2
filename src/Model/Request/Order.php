<?php

namespace Hipay\FullserviceMagento\Model\Request;

use Hipay\FullserviceMagento\Model\Request\AbstractRequest;
use Hipay\Fullservice\Gateway\Request\Order\OrderRequest;

/**
 * @author kassim
 *
 */
class Order extends AbstractRequest{

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
		
		if (isset($params['config']) && $params['config'] instanceof HipayConfig) {
			$this->_config = $params['config'];
		} else {
			throw new \Exception('Config instance is required.');
		}
		
		if (isset($params['quote']) && $params['quote'] instanceof \Magento\Quote\Model\Quote) {
			$this->_quote = $params['quote'];
		} else {
			throw new \Exception('Quote instance is required.');
		}
		
	}
	
	/**
	 * @return \Hipay\Fullservice\Gateway\Request\Order\OrderRequest
	 */
	public function getRequestObject(){
		/* @var $httpRequest  \Magento\Framework\App\Request\Http */
		$httpRequest = $this->_context->getRequest();
		
		$orderRequest = new OrderRequest();
		$orderRequest->orderid = $this->_quote->getReservedOrderId();
		$orderRequest->operation = $this->_config->getPaymentAction();
		$orderRequest->payment_product = "cb"; //@TODO maybe display a payment product selection on frontend form ?
		$orderRequest->description = ""; //@TODO
		$orderRequest->long_description = "";
		$orderRequest->currency = $this->_quote->getBaseCurrencyCode();
		$orderRequest->amount = (float)$this->_quote->getBaseGrandTotal();
		$orderRequest->shipping = (float)$this->_quote->getShippingAddress()->getBaseShippingAmount();
		$orderRequest->tax = (float)$this->_quote->getBaseGrandTotal() - $this->_quote->getBaseSubtotal();
		$orderRequest->cid = $this->_customerId;
		$orderRequest->ipaddr = $this->_quote->getRemoteIp();
		
		$orderRequest->accept_url = "";
		$orderRequest->decline_url = "";
		$orderRequest->cancel_url ="" ;
		$orderRequest->exception_url = "";
		
		$orderRequest->http_accept = $httpRequest->getHeader('Accept');
		$orderRequest->http_user_agent = $httpRequest->getHeader('User-Agent');
		
		$orderRequest->device_fingerprint = "";
		
		$orderRequest->language = $this->_localeResolver->getLocale();
		
		$orderRequest->paymentMethod = ""; //@TODO
		
		$orderRequest->customerBillingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\BillingInfo');
		$orderRequest->customerShippingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\ShippingInfo');
		
		return $orderRequest;
		
	}

}