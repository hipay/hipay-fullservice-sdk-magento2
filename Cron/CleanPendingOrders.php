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

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use HiPay\FullserviceMagento\Model\Queue\CancelOrderApi\Publisher as CancelOrderApiPublisher;
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
     *
     * @var ManagerFactory $_gatewayManagerFactory
     */
    protected $_gatewayManagerFactory;

    /**
     * @var CancelOrderApiPublisher $_cancelOrderApiPublisher
     */
    protected $_cancelOrderApiPublisher;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Emulation
     */
    protected $_emulation;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

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
     * @param ManagerFactory                $gatewayManagerFactory
     * @param CancelOrderApiPublisher       $cancelOrderApiPublisher
     * @param State                         $state
     * @param Emulation                     $emulation
     * @param OrderRepositoryInterface      $orderRepository
     */
    public function __construct(
        OrderFactory $orderFactory,
        Data $paymentHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        OrderManagementInterface $orderManagement,
        DateTimeFactory $dateTimeFactory,
        StoreManagerInterface $storeManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        ManagerFactory $gatewayManagerFactory,
        CancelOrderApiPublisher $cancelOrderApiPublisher,
        State $state,
        Emulation $emulation,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_orderFactory = $orderFactory;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderManagement = $orderManagement;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->_gatewayManagerFactory = $gatewayManagerFactory;
        $this->_cancelOrderApiPublisher = $cancelOrderApiPublisher;
        $this->_state = $state;
        $this->_emulation  = $emulation;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Clean orders in pending status since 30 minutes
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $stores = $website->getStores();

            foreach ($stores as $store) {
                $storeId = $store->getId();
                $this->_emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
                $websiteId = $website->getId();
                $this->logger->info('Cleaning pending order for website ' . $storeId);
                $storesId = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);

                $paymentMethods = $this->paymentHelper->getPaymentMethods();
                $cancelPendingOrdersConfig = [];
                $pendingOrderStatusConfig = [];

                // Filter HiPay payment methods
                $hipayPaymentMethods = array_filter(array_keys($paymentMethods), function ($code) {
                    return strpos($code, 'hipay') !== false;
                });

                if (empty($hipayPaymentMethods)) {
                    $this->logger->info('No HiPay payment methods found for store ' . $storeId);
                    $this->_emulation->stopEnvironmentEmulation();
                    continue;
                }

                // Pre-fetch configuration values for HiPay payment methods
                foreach ($hipayPaymentMethods as $code) {
                    $cancelPendingOrdersConfig[$code] = $this->_scopeConfig->getValue(
                        'payment/' . $code . '/cancel_pending_order',
                        ScopeInterface::SCOPE_WEBSITE,
                        $websiteId
                    );
                    if ($cancelPendingOrdersConfig[$code]) {
                        $pendingOrderStatusConfig[$code] = $this->_scopeConfig->getValue(
                            'payment/' . $code . '/order_status',
                            ScopeInterface::SCOPE_WEBSITE,
                            $websiteId
                        );
                    }
                }

                /**
                 * @var Order $orderModel
                 */
                $orderModel = $this->_orderFactory->create();

                /**
                 * @var $collection OrderCollection
                 */
                $collection = $orderModel->getCollection();

                $caseStateConditions = [];

                // Build conditions for HiPay payment methods with cancel_pending_order enabled
                foreach ($hipayPaymentMethods as $code) {
                    if (empty($cancelPendingOrdersConfig[$code])) {
                        continue;
                    }

                    $status = $pendingOrderStatusConfig[$code];
                    $caseStateConditions[] = "(main_table.status = '$status' AND op.method = '$code')";
                }

                // Apply filters to the collection
                $targetStates = [Order::STATE_NEW, Order::STATE_PENDING_PAYMENT];

                $filteredHipayPaymentMethods = array_filter(
                    $hipayPaymentMethods,
                    function ($code) use ($cancelPendingOrdersConfig) {
                        return !empty($cancelPendingOrdersConfig[$code]);
                    }
                );

                $collection->getSelect()
                    ->where('op.method IN (?)', $filteredHipayPaymentMethods);
                //In case we have status not assigned to states pending/pending_payment
                if (!empty($caseStateConditions)) {
                    $collection->getSelect()->where(
                        new \Zend_Db_Expr(
                            "(main_table.state IN ('" . implode("','", $targetStates) . "')) " .
                            "OR (" . implode(' OR ', $caseStateConditions) . ")"
                        )
                    );
                } else {
                    $collection->getSelect()->where('main_table.state IN (?)', $targetStates);
                }

                // Add fields to select and join with payment table
                $collection->addFieldToSelect([
                    'entity_id',
                    'state',
                    'status',
                    'store_id',
                    'created_at',
                    'increment_id'
                ])
                    ->addFieldToFilter('main_table.store_id', ['in' => $storesId])
                    ->join(
                        ['op' => $orderModel->getResource()->getTable('sales_order_payment')],
                        'main_table.entity_id = op.parent_id',
                        ['method']
                    )
                    ->setPageSize(50);
                // Process orders
                if ($collection->getSize() >= 1) {
                    $this->logger->info('Found ' . $collection->getSize() . ' orders to process for store ' . $storeId);
                    foreach ($collection as $order) {
                        $this->cancelOrder($order);
                    }
                } else {
                    $this->logger->info('No orders to process for store ' . $storeId);
                }

                $this->_emulation->stopEnvironmentEmulation();
            }
        }

        return $this;
    }

    /**
     * Function Cancel order
     *
     * @param Order  $order
     * @param string $dateFormat
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function cancelOrder(Order $order, string $dateFormat = 'Y-m-d H:i:s')
    {
        $orderCreationTimeIsCancellable = true;

        $orderMethodInstance = $order->getPayment()->getMethodInstance();

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
                $message = __(
                    'Order canceled automatically by cron because order is pending since %1 minutes',
                    $messageInterval
                );

                $this->_orderManagement->cancel($order->getId());

                $orderStatus = $order->getPayment()->getMethodInstance()->getConfigData(
                    'order_status_payment_canceled'
                );

                $order->setState(Order::STATE_CANCELED)->setStatus($orderStatus);

                // keep order status/state
                $order->addCommentToStatusHistory(
                    $message,
                    $order->getStatus(),
                    true
                )->setIsCustomerNotified(false);

                $this->_orderRepository->save($order);

                $gatewayClient = $this->getGatewayManager($order);
                $payment = $order->getPayment();

                if (empty($payment->getCcTransId())) {
                    try {
                        $transId = $gatewayClient->getTransactionReference($order);
                        if ($transId !== null) {
                            $payment->setCcTransId($transId);
                            $order->save();
                        } else {
                            $this->logger->warning('No transaction ID found for order: ' . $order->getIncrementId());
                        }
                    } catch (Exception $e) {
                        $this->logger->error('Failed to retrieve transaction ID: ' . $e->getMessage());
                    }
                }

                if ($payment->getCcTransId()) {
                    try {
                        $gatewayClient->requestOperationCancel();
                    } catch (Exception $e) {
                        $this->logger->critical('Failed to cancel order: ' . $e->getMessage());
                        $this->_cancelOrderApiPublisher->execute((string) $order->getId());
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }

    /**
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return \HiPay\FullserviceMagento\Model\Gateway\Manager
     */
    protected function getGatewayManager($order)
    {
        return $this->_gatewayManagerFactory->create($order);
    }
}
