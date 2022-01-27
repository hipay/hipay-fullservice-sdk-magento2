<?php

/**
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\ThreeDS;

use HiPay\Fullservice\Enum\ThreeDSTwo\DeliveryTimeFrame;
use HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\ShippingIndicator;
use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement;
use Magento\Sales\Model\Order\Item;

/**
 * Account info
 *
 * @package HiPay\FullserviceMagento
 * @author HiPay <support@hipay.com>
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class MerchantRiskStatementFormatter extends AbstractRequest
{
    /**
     * @var \HiPay\FullserviceMagento\Helper\ThreeDSTwo
     */
    protected $_threeDSHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * MerchantRiskStatementFormatter constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper,
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

        $this->_threeDSHelper = $threeDSHelper;
        $this->_order = $params["order"];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @return MerchantRiskStatement
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $merchantRiskStatement = new MerchantRiskStatement();

        if ($this->_order->hasVirtualItems()) {
            $merchantRiskStatement->email_delivery_address = $this->_order->getCustomerEmail();
            $merchantRiskStatement->delivery_time_frame = DeliveryTimeFrame::ELECTRONIC_DELIVERY;
        }

        $merchantRiskStatement->purchase_indicator = $this->getPurchaseIndicator();

        if ($this->_threeDSHelper->isCustomerLoggedIn()) {
            $merchantRiskStatement->reorder_indicator = $this->cartAlreadyOrdered();
        }

        $merchantRiskStatement->shipping_indicator = $this->getShippingIndicator();

        return $merchantRiskStatement;
    }

    /**
     * @return int
     */
    protected function getPurchaseIndicator()
    {
        foreach ($this->_order->getItems() as $item) {
            if ($item->getStatusId() === Item::STATUS_BACKORDERED) {
                return PurchaseIndicator::FUTURE_AVAILABILITY;
            }
        }

        return PurchaseIndicator::MERCHANDISE_AVAILABLE;
    }

    /**
     * @return bool
     */
    protected function cartAlreadyOrdered()
    {
        return $this->_threeDSHelper->isReordered($this->_order, $this->_customerId, $this->getStoreId());
    }

    /**
     * @return int
     */
    protected function getShippingIndicator()
    {
        if ($this->_order->getIsVirtual()) {
            return ShippingIndicator::DIGITAL_GOODS;
        }

        if ($this->_threeDSHelper->billingAddressSameAsShipping($this->_checkoutData->getQuote())) {
            return ShippingIndicator::SHIP_TO_CARDHOLDER_BILLING_ADDRESS;
        } elseif (!$this->_threeDSHelper->isCustomerLoggedIn()) {
            return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
        } elseif ($this->_threeDSHelper->isAddressAlreadyUsed($this->_order->getShippingAddress())) {
            return ShippingIndicator::SHIP_TO_VERIFIED_ADDRESS;
        }

        return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->_checkoutData->getQuote()->getStoreId();
    }
}
