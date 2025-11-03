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

namespace HiPay\FullserviceMagento\Helper;

use HiPay\Fullservice\Enum\ThreeDSTwo\ReorderIndicator;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\FullserviceMagento\Model\Method\HostedFieldsMethod;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * ThreeDS v2 Helper class
 *
 * Provides utility methods for evaluating 3DS2 indicators and customer order history.
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ThreeDSTwo extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ThreeDSTwo constructor.
     *
     * @param Context             $context
     * @param Session             $session
     * @param CollectionFactory   $orderCollectionFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context             $context,
        Session             $session,
        CollectionFactory   $orderCollectionFactory,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->_session = $session;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * Check if a customer is currently logged in.
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_session->isLoggedIn();
    }

    /**
     * Retrieve all orders for a given customer and store.
     *
     * @param int            $customer
     * @param int            $store
     * @param \DateTime|null $dateLimit
     * @return Collection
     */
    public function getCustomerOrder($customer, $store, $dateLimit = null)
    {
        $orderCollection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_id', $customer)
            ->addAttributeToFilter('store_id', $store)
            ->setOrder('created_at', 'desc');

        if ($dateLimit !== null) {
            $orderCollection->addAttributeToFilter(
                'created_at',
                ['gteq' => $dateLimit->format('Y-m-d H:i:s')]
            );
        }

        return $orderCollection;
    }

    /**
     * Retrieve the most recent order for a given customer.
     *
     * @param int $customer
     * @param int $store
     * @return DataObject
     */
    public function getCustomerLatestOrder($customer, $store)
    {
        return $this->getCustomerOrder($customer, $store)->getFirstItem();
    }

    /**
     * Count customer orders for a given store and optional date limit.
     *
     * @param int            $customer
     * @param int            $store
     * @param \DateTime|null $dateLimit
     * @return int
     */
    public function getNbCustomerOrder($customer, $store, $dateLimit = null)
    {
        $orderCollection = $this->getCustomerOrder($customer, $store, $dateLimit);
        return $orderCollection->count();
    }

    /**
     * Count one-click payment attempts by the customer.
     *
     * @param int            $customer
     * @param int            $store
     * @param \DateTime|null $dateLimit
     * @return int
     */
    public function getNbOneclickAttempt($customer, $store, $dateLimit = null)
    {
        $count = 0;
        $orders = $this->getCustomerOrder($customer, $store, $dateLimit);

        foreach ($orders as $order) {
            if (
                $order->getPayment()->getAdditionalInformation('create_oneclick')
                && $order->getPayment()->getMethod() === HostedFieldsMethod::HIPAY_METHOD_CODE
            ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Determine if the current checkout is a recurring transaction.
     *
     * @param DataObject $checkoutData
     * @return bool
     */
    public function isRecurring($checkoutData)
    {
        return $checkoutData->getQuote()->getPayment()->getAdditionalInformation('eci') === ECI::RECURRING_ECOMMERCE;
    }

    /**
     * Get the first order date using a specific address.
     *
     * @param int $addressId
     * @param int $customer
     * @param int $store
     * @return int|null
     */
    public function getDateAddressFirstUsed($addressId, $customer, $store)
    {
        $orderCollection = $this->getOrdersByAddress($addressId, $customer, $store);
        $orderCollection->setOrder('created_at', 'asc');

        if ($orderCollection->count() > 0) {
            $order = $orderCollection->getFirstItem();
            return (int)date('Ymd', strtotime($order->getCreatedAt() ?: ''));
        }

        return null;
    }

    /**
     * Check if a shipping address has been used in previous orders.
     *
     * @param DataObject $shippingAddress
     * @return bool
     */
    public function isAddressAlreadyUsed($shippingAddress)
    {
        if ($shippingAddress->getCustomerAddressId() !== null) {
            $orderCollection = $this->getOrdersByAddress(
                $shippingAddress->getCustomerAddressId(),
                $shippingAddress->getCustomerId(),
                $shippingAddress->getOrder()->getStoreId()
            );

            if ($orderCollection->count() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if shipping and customer names are identical.
     *
     * @param DataObject $quote
     * @return bool
     */
    public function isIdenticalShippingName($quote)
    {
        return $quote->getCustomer()->getLastName() === $quote->getShippingAddress()->getLastName()
            && $quote->getCustomer()->getFirstname() === $quote->getShippingAddress()->getFirstname();
    }

    /**
     * Determine if the current order is a reorder.
     *
     * @param DataObject $currentOrder
     * @param int        $customer
     * @param int        $store
     * @return int
     */
    public function isReordered($currentOrder, $customer, $store)
    {
        $orders = $this->getCustomerOrder($customer, $store);

        if (count($orders) === 0) {
            return ReorderIndicator::FIRST_TIME_ORDERED;
        }

        $currentItems = [];

        foreach ($currentOrder->getItems() as $item) {
            $currentItems[] = [$item->getSku(), (int)$item->getQtyOrdered()];
        }

        foreach ($orders as $order) {
            if (count($order->getItems()) !== count($currentOrder->getItems())) {
                continue;
            }

            $countIdenticalItem = 0;

            foreach ($order->getItems() as $item) {
                $itemIdentifier = [$item->getSku(), (int)$item->getQtyOrdered()];

                if (in_array($itemIdentifier, $currentItems, true)) {
                    $countIdenticalItem++;
                }
            }

            if ($countIdenticalItem === count($currentOrder->getItems())) {
                return ReorderIndicator::REORDERED;
            }
        }

        return ReorderIndicator::FIRST_TIME_ORDERED;
    }

    /**
     * Verify if billing and shipping addresses are identical.
     *
     * @param DataObject $quote
     * @return bool
     */
    public function billingAddressSameAsShipping($quote)
    {
        if (!$quote->getShippingAddress()->getSameAsBilling()) {
            return !$this->isDifferentAddresses($quote->getShippingAddress(), $quote->getBillingAddress());
        }

        return true;
    }

    /**
     * Compare two addresses and check if they differ.
     *
     * @param DataObject $shipping
     * @param DataObject $billing
     * @return bool
     */
    private function isDifferentAddresses($shipping, $billing)
    {
        $shippingSerialized = $this->serializeAddress($shipping);
        $billingSerialized = $this->serializeAddress($billing);

        return strcmp($shippingSerialized, $billingSerialized) !== 0;
    }

    /**
     * Serialize address data into a string.
     *
     * @param DataObject $address
     * @return string
     */
    private function serializeAddress($address)
    {
        return $this->serializer->serialize(
            [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'country' => $address->getCountryId(),
                'company' => $address->getCompany(),
            ]
        );
    }

    /**
     * Retrieve all customer orders matching a specific shipping address.
     *
     * @param int $addressId
     * @param int $customer
     * @param int $store
     * @return Collection
     */
    private function getOrdersByAddress($addressId, $customer, $store)
    {
        $orderCollection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('customer_id')
            ->addAttributeToSelect('store_id')
            ->addAttributeToFilter('customer_id', $customer)
            ->addAttributeToFilter('store_id', $store);

        $orderCollection->getSelect()
            ->join(
                ['soa' => $orderCollection->getTable('sales_order_address')],
                'main_table.entity_id = soa.parent_id',
                ['customer_address_id']
            )
            ->where('soa.customer_address_id = ?', $addressId)
            ->where('soa.address_type = ?', 'shipping');

        return $orderCollection;
    }
}
