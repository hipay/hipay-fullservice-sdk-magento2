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

class AbstractRequest /*implements RequestInterface */{
	
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
	 * Checkout data
	 *
	 * @var \Magento\Checkout\Helper\Data
	 */
	protected $_checkoutData;
	
	
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;
	
	/**
	 * Config instance
	 *
	 * @var HipayConfig
	 */
	protected $_config;
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $_urlBuilder;

	
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
				$this->_logger = $logger;
				$this->_checkoutData = $checkoutData;
				$this->_checkoutSession = $checkoutSession;
				$this->_localeResolver = $localeResolver;
				$this->_requestFactory =  $requestFactory;
				$this->_urlBuilder = $urlBuilder;
				$this->_customerSession = isset($params['session'])
		            && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;
				
		        $this->_customerId = $this->_customerSession->getCustomerId();
		        
		        if (isset($params['config']) && $params['config'] instanceof HipayConfig) {
		        	$this->_config = $params['config'];
		        } else {
		        	throw new \Exception('Config instance is required.');
		        }
		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Hipay\FullserviceMagento\Model\Request\RequestInterface::getRequestObject()
	 */
	public function getRequestObject(){
		return $this->mapRequest();
	}
	
	/**
	 * Popualte sdk request object and return it
	 * @return \Hipay\Fullservice\Request\AbstractRequest
	 */
	//abstract protected function mapRequest();
	
	
	
}