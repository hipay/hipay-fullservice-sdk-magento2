<?php
/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Controller;

use Magento\Framework\App\Action\Action as AppAction;
use HiPay\FullserviceMagento\Model\Request\Type\Factory;

/**
 * Abstract Fullservice controller
 *
 * Abstract to all payment controllers
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class Fullservice extends AppAction
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_hipaySession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     *
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $logger;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Gateway\Factory
     */
    protected $_gatewayManagerFactory;

    /**
     *
     * @var  \HiPay\FullserviceMagento\Model\SecureVault\Factory
     */
    protected $_vaultManagerFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\Generic $hipaySession ,
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \HiPay\FullserviceMagento\Model\Checkout\Factory $checkoutFactory
     * @param Factory $requestfactory ,
     * @param \Psr\Log\LoggerInterface $logger
     * @param \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory
     * @param \HiPay\FullserviceMagento\Model\SecureVault\Factory $vaultManagerFactory
     * {@inheritDoc}
     *
     * @see \Magento\Backend\App\AbstractAction::__construct()
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $hipaySession,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory,
        \HiPay\FullserviceMagento\Model\SecureVault\Factory $vaultManagerFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_hipaySession = $hipaySession;

        $this->logger = $logger;
        $this->_gatewayManagerFactory = $gatewayManagerFactory;
        $this->_vaultManagerFactory = $vaultManagerFactory;

        parent::__construct($context);

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
     * HiPay session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    protected function _getSession()
    {
        return $this->_hipaySession;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function getResponse()
    {
        return $this->_response;
    }


}
