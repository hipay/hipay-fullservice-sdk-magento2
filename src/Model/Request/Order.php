<?php

namespace Hipay\FullserviceMagento\Model\Request;

use Hipay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use Hipay\Fullservice\Gateway\Request\Order\OrderRequest;
use Hipay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;

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
		
		
		if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
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
		$orderRequest->operation = $this->_config->getValue('paymentAction');
		$orderRequest->payment_product = "cb"; //@TODO maybe display a payment product selection on frontend form ?
		$orderRequest->description = sprintf("Order #%s",$this->_order->getIncrementId()); //@TODO
		$orderRequest->long_description = "";
		$orderRequest->currency = $this->_order->getBaseCurrencyCode();
		$orderRequest->amount = (float)$this->_order->getBaseGrandTotal();
		$orderRequest->shipping = (float)$this->_order->getShippingAmount();
		$orderRequest->tax = (float)$this->_order->getTaxAmount();
		$orderRequest->cid = $this->_customerId;
		$orderRequest->ipaddr = $this->_order->getRemoteIp();
		
		$orderRequest->accept_url = $this->_urlBuilder->getUrl('checkout/onepage/success');
		$orderRequest->pending_url = $this->_urlBuilder->getUrl('checkout/onepage/failure');
		$orderRequest->decline_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure');
		$orderRequest->cancel_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure'); 
		$orderRequest->exception_url =  $this->_urlBuilder->getUrl('checkout/onepage/failure');
		
		//$orderRequest->http_accept = $httpRequest->getHeader('Accept');
		//$orderRequest->http_user_agent = $httpRequest->getHeader('User-Agent');
		
		$orderRequest->device_fingerprint = "";
		
		$orderRequest->language = $this->_localeResolver->getLocale();
		
		//@TODO use payment method based on current payment method
		$cardTokenPaymentMethod = new CardTokenPaymentMethod();
		$cardTokenPaymentMethod->authentication_indicator = $this->_config->getValue('authentication_indicator');
		$orderRequest->paymentMethod = $cardTokenPaymentMethod;
		
		$orderRequest->customerBillingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\BillingInfo',['params' => ['order' => $this->_order,'config' => $this->_config]])->getRequestObject();
		$orderRequest->customerShippingInfo = $this->_requestFactory->create('\Hipay\FullserviceMagento\Model\Request\Info\ShippingInfo',['params' => ['order' => $this->_order,'config' => $this->_config]])->getRequestObject();
		
		return $orderRequest;
		
	}

}