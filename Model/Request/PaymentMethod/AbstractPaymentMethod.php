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

    /**
     *
     * @var \Magento\Quote\Model\QuoteFactory $_quoteFactory
     */
    protected $_quoteFactory;

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

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
            if ($this->_order->getQuote() === null) {
                $this->_quote = $this->_quoteFactory->create();
                $this->_quote->load($this->_order->getQuoteId());
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Order instance is required.'));
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->_quote;
    }
}
