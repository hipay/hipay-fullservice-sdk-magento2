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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\PaymentMethod;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\FullserviceMagento\Model\Request\Type\Factory;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Url;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Abstract Payment Method Request Object
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class AbstractPaymentMethod extends AbstractRequest
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     *
     * @var \Magento\Quote\Model\QuoteFactory $_quoteFactory
     */
    protected $_quoteFactory;

    /**
     * @param LoggerInterface                       $logger
     * @param Data                                  $checkoutData
     * @param Session                               $customerSession
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     * @param ResolverInterface                     $localeResolver
     * @param Factory                               $requestFactory
     * @param Url                                   $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param QuoteFactory                          $quoteFactory
     * @param array                                 $params
     * @throws LocalizedException
     */
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
        array $params = []
    ) {
        parent::__construct(
            $logger,
            $checkoutData,
            $customerSession,
            $checkoutSession,
            $localeResolver,
            $requestFactory,
            $urlBuilder,
            $helper,
            $params
        );

        $this->_quoteFactory = $quoteFactory;

        $helper->validateInstance(
            $params['order'] ?? null,
            Order::class,
            'Order instance is required.'
        );
        $this->_order = $params['order'];

        $this->_quote = $this->_order->getQuote();

        if ($this->_quote === null) {
            $this->_quote = $this->_quoteFactory->create()->load($this->_order->getQuoteId());
        }
    }

    /**
     * Get Quote
     *
     * @return Quote
     */
    protected function getQuote()
    {
        return $this->_quote;
    }
}
