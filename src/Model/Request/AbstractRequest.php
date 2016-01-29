<?php
/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace Hipay\FullserviceMagento\Model\Request;

use Hipay\FullserviceMagento\Model\Config as HipayConfig;
use Magento\Framework\App\Action\Context;

class AbstractRequest {
	
	/**
	 * @var \Magento\Quote\Model\Quote
	 */
	protected $_quote;
	
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;
	
	
	/**
	 * Customer ID
	 *
	 * @var int
	 */
	protected $_customerId;
	
	/**
	 * Order
	 *
	 * @var \Magento\Sales\Model\Order
	 */
	protected $_order;
	
	
	/**
	 * Checkout data
	 *
	 * @var \Magento\Checkout\Helper\Data
	 */
	protected $_checkoutData;
	
	/**
	 * Tax data
	 *
	 * @var \Magento\Tax\Helper\Data
	 */
	protected $_taxData;
	
	/**
	 * Customer data
	 *
	 * @var \Magento\Customer\Model\Url
	 */
	protected $_customerUrl;
	
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;
	
	
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;
	
	/**
	 * @var \Magento\Framework\UrlInterface
	 */
	protected $_coreUrl;
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;


	/**
	 * @var \Magento\Quote\Api\CartManagementInterface
	 */
	protected $_quoteManagement;
	
	/**
	 * @var \Magento\Quote\Model\Quote\TotalsCollector
	 */
	protected $_totalsCollector;
	
	/**
	 * Config instance
	 *
	 * @var HipayConfig
	 */
	protected $_config;
	
	/**
	 * @var \Magento\Framework\Url\Helper
	 */
	protected $_urlHelper;
	
	/**
	 * 
	 * @var Context
	 */
	protected $_context;
	
	/**
	 * @var \Magento\Framework\Locale\ResolverInterface
	 */
	protected $_localeResolver;
	
	/**
	 * 
	 * @var \Hipay\FullserviceMagento\Model\Request\Type\Factory
	 */
	protected $_requestFactory;
	
	public function __construct(
			\Magento\Framework\App\Action\Context $context,
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
			\Magento\Framework\Url\Helper\Data $urlHelper,
			\Magento\Framework\Locale\ResolverInterface $localeResolver,
			\Hipay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
			$params = []
			){
				$this->_context = $context;
				$this->_logger = $logger;
				$this->_customerUrl = $customerUrl;
				$this->_taxData = $taxData;
				$this->_checkoutData = $checkoutData;
				$this->_storeManager = $storeManager;
				$this->_coreUrl = $coreUrl;
				$this->quoteManagement = $quoteManagement;
				$this->_checkoutSession = $checkoutSession;
				$this->_totalsCollector = $totalsCollector;
				$this->_urlHelper = $urlHelper;
				$this->_localeResolver = $localeResolver;
				$this->_requestFactory =  $requestFactory;
				
				$this->_customerSession = isset($params['session'])
		            && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;
				
		        $this->_customerId = $this->_customerSession->getCustomerId();
		        
		     
		
	}
	
	/**
	 * Return sdk request object
	 * @see \Hipay\Fullservice\Request\AbstractRequest
	 */
	public function getRequestObject();
	
}