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

            $paymentMethods = $this->paymentHelper->getPaymentMethods();
            $cancelPendingOrdersConfig = [];
            $pendingOrderStatusConfig = [];

            // Pre-fetch configuration values for all payment methods
            foreach ($paymentMethods as $code => $data) {
                if ((strpos($code, 'hipay') !== false || strpos($code, 'hipay_cc') === false)) {
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

            // Build conditions for state and method filtering
            $stateConditions = [];
            // Build conditions for interval time of cancelation
            $intervalConditions = [];


            foreach ($paymentMethods as $code => $data) {
                if (isset($cancelPendingOrdersConfig[$code]) && $cancelPendingOrdersConfig[$code]) {
                    $stateConditions[] = [
                        'field' => 'main_table.state',
                        'value' => $pendingOrderStatusConfig[$code],
                        'method' => $code
                    ];

                    if ($code === 'hipay_hosted_fields') {
                        // one day interval
                        $intervalConditions[] = [
                            'value' => 1440,
                            'method' => $code
                        ];
                    } else if (strpos($code, 'alma') !== false) {
                        // two days interval
                        $intervalConditions[] = [
                            'value' => 2880,
                            'method' => $code
                        ];
                    } else {
                        // default interval
                        $intervalConditions[] = [
                            'value' => 30,
                            'method' => $code
                        ];
                    }
                }
            }

            // Construct the CASE conditions for state and method
            $caseStateConditions = [];

            foreach ($stateConditions as $condition) {
                $state = $condition['value'];
                $method = $condition['method'];
                $caseStateConditions[] = "(main_table.state = '$state' AND op.method = '$method')";
            }

            // Construct the CASE conditions for interval
            $caseIntervalConditions = [];
            foreach ($intervalConditions as $condition) {
                $interval = new DateInterval("PT{$condition['value']}M");;
                $method = $condition['method'];
                $formattedDate = $date->sub($interval)->format($dateFormat);
                $caseIntervalConditions[] = "(main_table.created_at <= '$formattedDate' AND op.method = '$method')";
            }

            // Combine CASE conditions into a single condition
            $caseConditionString = implode(' OR ', $caseStateConditions);
            $caseIntervalConditionsString = implode(' OR ', $caseIntervalConditions);

            $collection->addFieldToSelect(['entity_id', 'state', 'status', 'store_id', 'created_at'])
                ->addFieldToFilter('main_table.store_id', ['in' => $storesId])
                ->join(
                    ['op' => $orderModel->getResource()->getTable('sales_order_payment')],
                    'main_table.entity_id = op.parent_id',
                    ['method']
                )
                ->setPageSize(50);

            $collection->getSelect()
                ->where(new \Zend_Db_Expr('(' . $caseConditionString . ')'))
                ->where(new \Zend_Db_Expr('(' . $caseIntervalConditionsString . ')'));

            //var_dump((string) $collection->getSelect());
            //die("here");
            if ($collection->getSize() >= 1) {
                foreach ($collection as $order) {
                    $method = $order->getMethod();
                    if (isset($intervalConditions[$method])) {
                        $interval = new DateInterval("PT{$intervalConditions[$method]}M");
                    } else {
                        $interval = new DateInterval("PT30M"); // default interval if method not found
                    }
                    $this->cancelOrder($order, $interval, $dateFormat);
                }
            }
        }

        return $this;
    }


    /**
     * Function Cancel order
     *
     * @param Order $order
     * @param DateInterval|null $interval
     * @param string|null $dateFormat
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function cancelOrder(Order $order, ?DateInterval $interval, ?string $dateFormat = 'Y-m-d H:i:s')
    {
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
                $this->_orderManagement->cancel($order->getId());

                $orderStatus = $order->getPayment()->getMethodInstance()->getConfigData(
                    'order_status_payment_canceled'
                );

                // keep order status/state
                $order
                    ->addStatusToHistory(
                        $orderStatus,
                        __(
                            'Order canceled automatically by cron because order ' .
                            'is pending since %1 minutes',
                            $messageInterval
                        )
                    );

                $order->setState(Order::STATE_CANCELED)->setStatus($orderStatus);

                $order->save();
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
