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

namespace HiPay\FullserviceMagento\Model\Request;

use HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest as MaintenanceRequest;
use HiPay\FullserviceMagento\Model\Cart\CartFactory;
use HiPay\FullserviceMagento\Model\Request\Type\Factory;
use HiPay\FullserviceMagento\Model\ResourceModel\MappingCategories\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Url;
use Psr\Log\LoggerInterface;

/**
 * Maintenance Request Object
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Maintenance extends CommonRequest
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \HiPay\Fullservice\Request\AbstractRequest
     */
    protected $_paymentMethod;

    /**
     * @var \HiPay\FullserviceMagento\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     *  Operation type
     *
     * @var string $operation
     */
    protected $_operation;

    /**
     * @var CartFactory
     */
    protected $_cartFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @inheritDoc
     *
     * @param LoggerInterface $logger
     * @param Data $checkoutData
     * @param Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ResolverInterface $localeResolver
     * @param Factory $requestFactory
     * @param Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param CartFactory $cartFactory
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param CollectionFactory $mappingCategoriesCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param array $params
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
        \HiPay\FullserviceMagento\Model\Cart\CartFactory $cartFactory,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        CollectionFactory $mappingCategoriesCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
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
            $cartFactory,
            $weeeHelper,
            $productRepositoryInterface,
            $mappingCategoriesCollectionFactory,
            $categoryFactory,
            $params
        );

        $this->helper = $helper;
        $this->_cartFactory = $cartFactory;
        $this->weeeHelper = $weeeHelper;
        $this->_productRepositoryInterface = $productRepositoryInterface;

        if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
            $this->_order = $params['order'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Order instance is required.'));
        }

        if (isset($params['operation'])) {
            $this->_operation = $params['operation'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Operation  is required.'));
        }

        if (isset($params['paymentMethod'])
            && $params['paymentMethod'] instanceof \HiPay\Fullservice\Request\AbstractRequest
        ) {
            $this->_paymentMethod = $params['paymentMethod'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Object Request PaymentMethod instance is required.')
            );
        }
    }

    /**
     *  Map Request Object for transaction
     *
     * @return \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest
     */
    protected function mapRequest()
    {
        $maintenanceRequest = new MaintenanceRequest();
        $payment_product = $this->_order->getPayment()->getMethodInstance()->getConfigData('payment_products');
        if ($this->_config->isNecessaryToSendCartItems($payment_product)) {
            $useOrderCurrency = $this->_order->getPayment()->getMethodInstance()->isDifferentCurrency(
                $this->_order->getPayment()
            );
            $maintenanceRequest->basket = $this->processCartFromOrder($this->_operation, $useOrderCurrency);
        }
        // Technical parameter to track wich magento version is used
        $maintenanceRequest->source = $this->helper->getRequestSource();
        return $maintenanceRequest;
    }
}
