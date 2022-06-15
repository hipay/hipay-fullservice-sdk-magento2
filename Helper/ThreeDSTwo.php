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
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * ThreeDS v2 Helper class
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ThreeDSTwo extends AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    protected $_session;

    /**
     * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory $_spFactory
     */
    protected $_ppFactory;

    /**
     * @var \HiPay\FullserviceMagento\Model\SplitPaymentFactory $_spFactory
     */
    protected $_spFactory;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        CollectionFactory $orderCollectionFactory,
        \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory,
        \HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory
    ) {
        parent::__construct($context);
        $this->_session = $session;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_ppFactory = $ppFactory;
        $this->_spFactory = $spFactory;
    }

    public function isCustomerLoggedIn()
    {
        return $this->_session->isLoggedIn();
    }

    public function getCustomerOrder($customer, $store, $dateLimit = null)
    {
        $orderCollection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_id', $customer)
            ->addAttributeToFilter('store_id', $store)
            ->setOrder('created_at', 'desc');

        if ($dateLimit !== null) {
            $orderCollection->addAttributeToFilter('created_at', ['gteq' => $dateLimit->format('Y-m-d H:i:s')]);
        }

        return $orderCollection;
    }

    public function getCustomerLatestOrder($customer, $store)
    {
        return $this->getCustomerOrder($customer, $store)->getFirstItem();
    }

    public function getNbCustomerOrder($customer, $store, $dateLimit = null)
    {
        $orderCollection = $this->getCustomerOrder($customer, $store, $dateLimit);

        return $orderCollection->count();
    }

    public function getNbOneclickAttempt($customer, $store, $dateLimit = null)
    {
        $count = 0;

        $orders = $this->getCustomerOrder($customer, $store, $dateLimit);

        foreach ($orders as $order) {
            if ($order->getPayment()->getAdditionalInformation("create_oneclick")) {
                $count++;
            }
        }

        return $count;
    }

    public function isRecurring($checkoutData)
    {
        return $checkoutData->getQuote()->getPayment()->getAdditionalInformation('eci') == ECI::RECURRING_ECOMMERCE;
    }

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

    public function isIdenticalShippingName($quote)
    {
        return $quote->getCustomer()->getLastName() === $quote->getShippingAddress()->getLastName()
            && $quote->getCustomer()->getFirstname() === $quote->getShippingAddress()->getFirstname();
    }

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

                if (in_array($itemIdentifier, $currentItems)) {
                    $countIdenticalItem++;
                }
            }

            if ($countIdenticalItem === count($currentOrder->getItems())) {
                return ReorderIndicator::REORDERED;
            }
        }

        return ReorderIndicator::FIRST_TIME_ORDERED;
    }

    public function billingAddressSameAsShipping($quote)
    {
        if (!$quote->getShippingAddress()->getSameAsBilling()) {
            return !$this->isDifferentAddresses($quote->getShippingAddress(), $quote->getBillingAddress());
        }

        return true;
    }

    /**
     * @param  $profileId
     * @return mixed
     * @throws LocalizedException
     */
    public function getPaymentProfile($profileId)
    {
        $profile = $this->_ppFactory->create();
        $profile->load($profileId);

        if (!$profile->getId()) {
            throw new LocalizedException(__('Payment Profile not found.'));
        }

        return $profile;
    }

    /**
     * @param  $orderId
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     */
    public function getLastOrderSplitPayment($orderId)
    {
        $splitPayments = $this->getOrderSplitPaymentCollection($orderId);

        return $splitPayments->getLastItem();
    }

    /**
     * @param  $orderId
     * @return bool|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getOrderSplitPaymentCollection($orderId)
    {
        $splitPayments = $this->_spFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('date_to_pay', 'asc');

        if (count($splitPayments->getItems()) === 0) {
            return false;
        }

        return $splitPayments;
    }

    private function isDifferentAddresses($shipping, $billing)
    {
        $shippingSerialized = $this->serializeAddress($shipping);
        $billingSerialized = $this->serializeAddress($billing);

        return strcmp($shippingSerialized, $billingSerialized) !== 0;
    }

    private function serializeAddress($address)
    {
        return serialize(
            array(
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'country' => $address->getCountryId(),
                'company' => $address->getCompany(),
            )
        );
    }

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
                array('customer_address_id')
            )
            ->where('soa.customer_address_id = ?', $addressId)
            ->where('soa.address_type = ?', 'shipping');

        return $orderCollection;
    }
}
