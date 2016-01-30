<?php

namespace Hipay\FullserviceMagento\Model\Request;

use Hipay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use Hipay\Fullservice\Gateway\Request\Order\OrderRequest;

/**
 * @author kassim
 *
 */
class Order extends BaseRequest{
	
	/**
	 * Order
	 *
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;

	/**
	 * {@inheritDoc}
	 * @see \Hipay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
	 */
	public function __construct(
			\Psr\Log\LoggerInterface $logger,
			\Magento\Checkout\Helper\Data $checkoutData,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\Locale\ResolverInterface $localeResolver,
			\Hipay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
			\Magento\Framework\Url $urlBuilder,
			$params = []
			)
	{
		
		parent::__construct($logger, $checkoutData, $customerSession, $checkoutSession, $localeResolver, $requestFactory, $urlBuilder,$params);

		
		if (isset($params['order']) && $params['order'] instanceof \Magento\Quote\Model\Order) {
			$this->_order = $params['order'];
		} else {
			throw new \Exception('Order instance is required.');
		}
		
	}
	
	/**
	 * @return \Hipay\Fullservice\Gateway\Request\Order\OrderRequest
	 */
	protected function mapRequest(){
		/* @var $httpRequest  \Magento\Framework\App\Request\Http */
		//$httpRequest = $this->_context->getRequest();
		
		$orderRequest = new OrderRequest();
		$orderRequest->orderid = $this->_order->getIncrementId();
		$orderRequest->operation = $this->_config->getPaymentAction();
		$orderRequest->payment_product = "cb"; //@TODO maybe display a payment product selection on frontend form ?
		$orderRequest->description = ""; //@TODO
		$orderRequest->long_description = "";
		$orderRequest->currency = $this->_order->getBaseCurrencyCode();
		$orderRequest->amount = (float)$this->_order->getBaseGrandTotal();
		$orderRequest->shipping = (float)$this->_order->getShippingAmount();
		$orderRequest->tax = (float)$this->_order->getTaxAmount();
		$orderRequest->cid = $this->_customerId;
		$orderRequest->ipaddr = $this->_order->getRemoteIp();
		
		$orderRequest->accept_url = $this->_urlBuilder->getUrl('checkout/onepage/success');
		$orderRequest->decline_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure');
		$orderRequest->cancel_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure'); 
		$orderRequest->exception_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure');
		
		//$orderRequest->http_accept = $httpRequest->getHeader('Accept');
		//$orderRequest->http_user_agent = $httpRequest->getHeader('User-Agent');
		
		$orderRequest->device_fingerprint = "";
		
		$orderRequest->language = $this->_localeResolver->getLocale();
		
		$orderRequest->paymentMethod = ""; //@TODO
		
		$orderRequest->customerBillingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\BillingInfo');
		$orderRequest->customerShippingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\ShippingInfo');
		
		return $orderRequest;
		
	}

}