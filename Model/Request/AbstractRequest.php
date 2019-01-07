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
namespace HiPay\FullserviceMagento\Model\Request;

use HiPay\FullserviceMagento\Model\Config as HiPayConfig;

/**
 * Abstract Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class AbstractRequest implements RequestInterface
{

    /**
     * @var \Magento\Customer\Model\Session $_customerSession
     */
    protected $_customerSession;

    /**
     * Customer ID
     *
     * @var int $_customerId
     */
    protected $_customerId;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data $_checkoutData
     */
    protected $_checkoutData;

    /**
     * @var \Psr\Log\LoggerInterface $_logger
     */
    protected $_logger;

    /**
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * Config instance
     *
     * @var HiPayConfig $_config
     */
    protected $_config;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url $_urlBuilder
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface $_localeResolver
     */
    protected $_localeResolver;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Request\Type\Factory $_requestFactory
     */
    protected $_requestFactory;

    /**
     *
     * @var \Magento\Quote\Model\QuoteFactory $_quoteFactory
     */
    protected $_quoteFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Helper\Data $_helper
     */
    protected $_helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        $params = []
    ) {
        $this->_logger = $logger;
        $this->_checkoutData = $checkoutData;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeResolver = $localeResolver;
        $this->_requestFactory = $requestFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_helper = $helper;

        $this->_customerSession = isset($params['session'])
        && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;

        $this->_customerId = $this->_customerSession->getCustomerId();

        if (isset($params['config']) && $params['config'] instanceof HiPayConfig) {
            $this->_config = $params['config'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Config instance is required.'));
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \HiPay\FullserviceMagento\Model\Request\RequestInterface::getRequestObject()
     */
    public function getRequestObject()
    {
        return $this->mapRequest();
    }

    /**
     * Popualte sdk request object and return it
     * @return \HiPay\Fullservice\Request\AbstractRequest
     */
    abstract protected function mapRequest();
}
