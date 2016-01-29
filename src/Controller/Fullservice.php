<?php

namespace Hipay\FullserviceMagento\Controller;

use Magento\Framework\App\Action\Action as AppAction;

/**
 *
 * @author kassim
 *        
 */
abstract class Fullservice extends AppAction {
	
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;
	
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;
	
	/**
	 * @var \Magento\Framework\Url\Helper
	 */
	protected $_urlHelper;
	
	/**
	 * @var \Magento\Framework\Session\Generic
	 */
	protected $_hipaySession;
	
	/**
	 * @var \Hipay\FullserviceMagento\Model\Checkout\Factory
	 */
	protected $_checkoutFactory;
	

	/**
	 * @var \Hipay\FullserviceMagento\Model\Checkout\AbstractCheckout
	 */
	protected $_checkout;
	
	/**
	 * Checkout mode type
	 *
	 * @var string
	 */
	protected $_checkoutType;
	
	/**
	 * Internal cache of checkout models
	 *
	 * @var array
	 */
	protected $_checkoutTypes = [];
	
	/**
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod;
	
	/**
	 * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Hipay\FullserviceMagento\Model\Checkout\Factory $checkoutFactory
	 * {@inheritDoc}
	 *
	 * @see \Magento\Backend\App\AbstractAction::__construct()
	 */
	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\Session\Generic $hipaySession,
			\Magento\Framework\Url\Helper\Data $urlHelper,
			\Hipay\FullserviceMagento\Model\Checkout\Factory $checkoutFactory
	) {
		$this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession; 
        $this->_hipaySession = $hipaySession;
        $this->_urlHelper = $urlHelper;
        $this->_checkoutFactory = $checkoutFactory;

        parent::__construct($context);
        
        $parameters = ['params' => [$this->_configMethod]];
        $this->_config = $this->_objectManager->create($this->_configType, $parameters);

	}
	
	/**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Express Checkout.'));
        }
        if (!isset($this->_checkoutTypes[$this->_checkoutType])) {
            $parameters = [
                'params' => [
                    'quote' => $quote,
                    'config' => $this->_config,
                ],
            ];
            $this->_checkoutTypes[$this->_checkoutType] = $this->_checkoutFactory
                ->create($this->_checkoutType, $parameters);
        }
        $this->_checkout = $this->_checkoutTypes[$this->_checkoutType];
    }
	
	 /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }
    
    /**
     * Hipay session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    protected function _getSession()
    {
    	return $this->_hipaySession;
    }
    
    /**
     * Returns action name which requires redirect
     * @return string
     */
    public function getRedirectActionName()
    {
    	return 'start';
    }

}