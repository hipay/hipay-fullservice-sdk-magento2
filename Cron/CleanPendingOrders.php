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

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
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
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\TranslateInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Language\Dictionary;
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use HiPay\FullserviceMagento\Model\Queue\CancelOrderApi\Publisher as CancelOrderApiPublisher;
use DateTime;
use DateInterval;
use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\AreaList;

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
     * @var AreaList
     */
    protected $_areaList;

    /**
     * CleanPendingOrders constructor
     *
     * @param OrderFactory $orderFactory
     * @param Data $paymentHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param OrderManagementInterface $orderManagement
     * @param DateTimeFactory $dateTimeFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param ManagerFactory $gatewayManagerFactory
     * @param CancelOrderApiPublisher $cancelOrderApiPublisher
     * @param State $state
     * @param Emulation $emulation
     * @param AreaList $areaList
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
        AreaList $areaList
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
        $this->_areaList = $areaList;
    }

    /**
     * Clean orders in pending status since 30 minutes
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->_state->setAreaCode(Area::AREA_FRONTEND);
        $areaModel = $this->_areaList->getArea($this->_state->getAreaCode());

        // Load design and translation parts
        $areaModel->load(AreaInterface::PART_DESIGN);
        $areaModel->load(AreaInterface::PART_TRANSLATE);

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

                        if ($code == 'hipay_hosted_fields') {
                            // one day interval
                            $intervalConditions[] = [
                                'value' => 1,
                                'method' => $code
                            ];
                        } else if (strpos($code, 'alma') !== false) {
                            // two days interval
                            $intervalConditions[] = [
                                'value' => 1,
                                'method' => $code
                            ];
                        } else {
                            // default interval
                            $intervalConditions[] = [
                                'value' => 1,
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
                    $caseStateConditions[] = "(main_table.status = '$state' AND op.method = '$method')";
                }

                // Construct the CASE conditions for interval
                $caseIntervalConditions = [];
                $dateFormat = 'Y-m-d H:i:s';
                foreach ($intervalConditions as $condition) {
                    $dateObject = $this->_dateTimeFactory->create();
                    $gmtDate = $dateObject->gmtDate($dateFormat);
                    $date = new DateTime($gmtDate);
                    $interval = new DateInterval("PT{$condition['value']}M");;
                    $method = $condition['method'];
                    $formattedDate = $date->sub($interval)->format($dateFormat);
                    $caseIntervalConditions[] = "(main_table.created_at <= '$formattedDate' AND op.method = '$method')";
                }

                $collection->addFieldToSelect(['entity_id', 'state', 'status', 'store_id', 'created_at'])
                    ->addFieldToFilter('main_table.store_id', ['in' => $storesId])
                    ->join(
                        ['op' => $orderModel->getResource()->getTable('sales_order_payment')],
                        'main_table.entity_id = op.parent_id',
                        ['method']
                    )
                    ->setPageSize(50);

                // Combine CASE conditions into a single condition
                if (count($caseStateConditions) > 33 && count($caseIntervalConditions) > 33) {
                    $caseConditionString = implode(' OR ', $caseStateConditions);
                    $caseIntervalConditionsString = implode(' OR ', $caseIntervalConditions);

                    $collection->getSelect()
                        ->where(new \Zend_Db_Expr('(' . $caseConditionString . ')'))
                        ->where(new \Zend_Db_Expr('(' . $caseIntervalConditionsString . ')'));
                }

                if ($collection->getSize() >= 1) {
                    // Build the method to interval map
                    $methodIntervals = [];
                    foreach ($intervalConditions as $condition) {
                        $methodIntervals[$condition['method']] = $condition['value'];
                    }
                    foreach ($collection as $order) {
                        $method = $order->getMethod();
                        $intervalValue = $methodIntervals[$method] ?? 30;
                        $interval = new DateInterval("PT{$intervalValue}M");
                      //  $this->cancelOrder($order, $interval, $dateFormat);
                    }
                }


                $this->_emulation->stopEnvironmentEmulation();

            }
            //die("here");



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

        if ($order) {
            try {
                $message = __('Order canceled automatically by cron because order is pending since %1 minutes'
                    , $messageInterval);

                $this->_orderManagement->cancel($order->getId());

                $orderStatus = $order->getPayment()->getMethodInstance()->getConfigData(
                    'order_status_payment_canceled'
                );

                $order->setState(Order::STATE_CANCELED)->setStatus($orderStatus);

                // keep order status/state
                $history = $order->addCommentToStatusHistory($message,
                    $order->getStatus(),
                    true
                );
                $history->setIsCustomerNotified(false);

                $history->save();
                $order->save();

                $this->_orderManagement->addComment($order->getId(),$history);

                //Cancel through API
                if (!empty($order->getPayment()->getCcTransId())) {
                    try {
                        $this->getGatewayManager($order)->requestOperationCancel();
                    } catch (Exception $e) {
                        $this->_cancelOrderApiPublisher->execute((string) $order->getId());
                        $this->logger->critical($e->getMessage());
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
