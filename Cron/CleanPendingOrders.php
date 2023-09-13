<?php

/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
 
namespace HiPay\FullserviceMagento\Cron;

use Magento\Payment\Helper\Data;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Model\Order;
use DateTime;
use DateInterval;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * HiPay module crontab
 *
 * Used to clean orders in pending or pending review since more than 30 minutes
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CleanPendingOrders
{
    /**
     * @var Data $paymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderFactory $_orderFactory
     */
    protected $_orderFactory;

    /**
     * @var OrderManagementInterface $_orderManagement
     */
    protected $_orderManagement;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var DateTimeFactory
     */
    protected $_dateTimeFactory;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    protected $storeWebsiteRelation;

    /**
     * CleanPendingOrders constructor
     *
     * @param OrderFactory                  $orderFactory
     * @param Data                          $paymentHelper
     * @param ScopeConfigInterface          $scopeConfig
     * @param LoggerInterface               $logger
     * @param OrderManagementInterface      $orderManagement
     * @param DateTimeFactory               $dateTimeFactory
     * @param StoreManagerInterface         $storeManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        OrderFactory $orderFactory,
        Data $paymentHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        OrderManagementInterface $orderManagement,
        DateTimeFactory $dateTimeFactory,
        StoreManagerInterface $storeManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->_orderFactory = $orderFactory;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderManagement = $orderManagement;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * Clean orders in pending status since 30 minutes
     *
     * @return $this
     */
    public function execute()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $websiteId = $website->getId();
            $this->logger->info('Cleaning pending order for website ' . $websiteId);
            $storesId = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);


            $methodCodes = $this->getHipayMethods($websiteId);
            $hostedMethodCodes = $this->getHostedHipayMethods($websiteId);

            if (count($methodCodes) < 1) {
                return $this;
            }

            //Limited time in minutes
            $limitedTime = 30;

            $dateFormat = 'Y-m-d H:i:s';
            $dateObject = $this->_dateTimeFactory->create();
            $gmtDate = $dateObject->gmtDate($dateFormat);
            $date = new DateTime($gmtDate);
            $interval = new DateInterval("PT{$limitedTime}M");

            /**
             * @var Order $orderModel
             */
            $orderModel = $this->_orderFactory->create();

            /**
             * @var $collection OrderCollection
             */
            $collection = $orderModel->getCollection();

            $collection->addFieldToSelect('*')
                ->addFieldToFilter(
                    'main_table.state',
                    ['in' => [Order::STATE_NEW, Order::STATE_PENDING_PAYMENT]]
                )
                ->addFieldToFilter('op.method', ['in' => array_values($methodCodes)])
                ->addFieldToFilter('main_table.store_id', ['in' => $storesId])
                ->addAttributeToFilter('created_at', ['to' => ($date->sub($interval)->format($dateFormat))])
                ->join(
                    ['op' => $orderModel->getResource()->getTable('sales_order_payment')],
                    'main_table.entity_id=op.parent_id',
                    ['method']
                );

            /**
             * @var Order $order
             */
            foreach ($collection as $order) {
                if (
                    $order->getState() === Order::STATE_NEW
                    || $order->getState() === Order::STATE_PENDING_PAYMENT
                    || in_array($order->getPayment()->getMethod(), array_values($hostedMethodCodes))
                ) {
                    $orderCreationTimeIsCancellable = true;

                    $orderMethodInstance = $order->getPayment()->getMethodInstance();
                    $messageInterval = $interval->i;

                    if (isset($orderMethodInstance->overridePendingTimeout)) {
                        $messageInterval = $orderMethodInstance->overridePendingTimeout;
                        $dateObject = $this->_dateTimeFactory->create();
                        $gmtDate = $dateObject->gmtDate($dateFormat);
                        $date = new DateTime($gmtDate);
                        $intervalMethod = new DateInterval("PT{$messageInterval}M");
                        $cancellationTime = $date->sub($intervalMethod);
                        $orderDate = DateTime::createFromFormat($dateFormat, $order->getCreatedAt());

                        if ($orderDate->format($dateFormat) > $cancellationTime->format($dateFormat)) {
                            $orderCreationTimeIsCancellable = false;
                        }
                    }

                    if ($orderCreationTimeIsCancellable && $order->canCancel()) {
                        try {
                            $order->cancel();
                            // keep order status/state
                            $order
                                ->addStatusToHistory(
                                    $order->getStatus(),
                                    __(
                                        'Order canceled automatically by cron because order ' .
                                        'is pending since %1 minutes',
                                        $messageInterval
                                    )
                                );

                            $order->save();

                            $this->_orderManagement->cancel($order->getId());
                        } catch (Exception $e) {
                            $this->logger->critical($e->getMessage());
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve enable hipay method
     *
     * @param int|null $websiteId
     * @return array
     */
    public function getHipayMethods(?int $websiteId = null)
    {
        $methods = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (
                strpos($code, 'hipay') !== false
                && $this->_scopeConfig->getValue(
                    'payment/' . $code . '/cancel_pending_order',
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                )
            ) {
                    $methods[] = $code;
            }
        }

        return $methods;
    }

    /**
     * Retrieve enable hosted hipay method
     *
     * @param int|null $websiteId
     * @return array
     */
    public function getHostedHipayMethods(?int $websiteId = null)
    {
        $methods = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (
                strpos($code, 'hipay') !== false
                && strpos($code, 'hipay_cc') === false
                && $this->_scopeConfig->getValue(
                    'payment/' . $code . '/cancel_pending_order',
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                )
            ) {
                    $methods[] = $code;
            }
        }

        return $methods;
    }
}
