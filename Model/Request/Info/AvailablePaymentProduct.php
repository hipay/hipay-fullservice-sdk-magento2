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

namespace HiPay\FullserviceMagento\Model\Request\Info;

use HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest as AvailablePaymentProductRequest;
use HiPay\FullserviceMagento\Model\Config as HiPayConfig;
use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\FullserviceMagento\Model\Request\CommonRequest;
use HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory;

/**
 * Available Payment Product
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AvailablePaymentProduct extends AbstractRequest
{
    /**
     *  Payment Product
     *
     * @var array $operation
     */
    protected $payment_product;

    /**
     *  With options
     *
     * @var bool $operation
     */
    protected $with_options;

    /**
     * {@inheritDoc}
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
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

        $this->payment_product = $params["payment_product"];
        $this->with_options = $params["with_options"];
    }

    /**
     *  Map Request Object for transaction
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest
     */
    protected function mapRequest()
    {
        $available_payment_product = new AvailablePaymentProductRequest();

        $available_payment_product->payment_product = $this->payment_product;
        $available_payment_product->with_options = $this->with_options;

        return $available_payment_product;
    }
}
