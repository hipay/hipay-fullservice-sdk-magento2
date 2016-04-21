<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Model\Request\PaymentMethod;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
abstract class AbstractPaymentMethod extends AbstractRequest{
	
	
	/**
	 * Order
	 *
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	/**
	 * 
	 * @var \Magento\Quote\Model\Quote
	 */
	protected $_quote;
	
	public function __construct(
			\Psr\Log\LoggerInterface $logger,
			\Magento\Checkout\Helper\Data $checkoutData,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\Locale\ResolverInterface $localeResolver,
			\HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
			\Magento\Framework\Url $urlBuilder,
			\HiPay\FullserviceMagento\Helper\Data $helper,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
			$params = []
	
			)
	{
	
		parent::__construct( $logger, $checkoutData, $customerSession, $checkoutSession, $localeResolver, $requestFactory, $urlBuilder,$helper,$quoteFactory ,$params);
	
		if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
			$this->_order = $params['order'];
			if(is_null($this->_order->getQuote())){
				$this->_quote = $this->_quoteFactory->create()->load($this->_order->getQuoteId());
			}
		} else {
			throw new \Exception('Order instance is required.');
		}
	}
	
	/**
	 * @return \Magento\Quote\Model\Quote
	 */
	protected function getQuote(){
		return $this->_quote;
	}
	
}