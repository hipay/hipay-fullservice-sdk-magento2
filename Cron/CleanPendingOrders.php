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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

namespace HiPay\FullserviceMagento\Cron;

/**
 * HiPay module crontab
 *
 * Used to clean orders in pending or pending review since more than 30 minutes
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CleanPendingOrders
{

    /**
     *
     * @var \Magento\Payment\Helper\Data $paymentHelper ;
     */
    protected $paymentHelper;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory $_orderFactory
     */
    protected $_orderFactory;

    /**
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     *
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_orderFactory = $orderFactory;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Clean orders in pending status since 30 minutes
     *
     * @return $this
     */
    public function execute()
    {
        $methodCodes = $this->getHipayMethods();
        $hostedMethodCodes = $this->getHostedHipayMethods();

        if (count($methodCodes) < 1) {
            return $this;
        }

        //Limited time in minutes
        $limitedTime = 30;

        $date = new \DateTime();
        $interval = new \DateInterval("PT{$limitedTime}M");

        /** @var \Magento\Sales\Model\Order $orderModel */
        $orderModel = $this->_orderFactory->create();

        /** @var $collection \Magento\Sales\Model\ResourceModel\Order\Collection */
        $collection = $orderModel->getCollection();

        $collection->addFieldToSelect(array('entity_id', 'increment_id', 'store_id', 'state', 'created_at'))
            ->addFieldToFilter('main_table.state', array('in' => [\Magento\Sales\Model\Order::STATE_NEW, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]))
            ->addFieldToFilter('op.method', array('in' => array_values($methodCodes)))
            ->addAttributeToFilter('created_at', array('to' => ($date->sub($interval)->format('Y-m-d H:i:s'))))
            ->join(
                array('op' => $orderModel->getResource()->getTable('sales_order_payment')),
                'main_table.entity_id=op.parent_id',
                array('method')
            );

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            $this->logger->critical($order->getState());

            if($order->getState() === \Magento\Sales\Model\Order::STATE_NEW || $order->getState() === \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT ||
            in_array($order->getPayment()->getMethod(), array_values($hostedMethodCodes))) {
                $orderCreationTimeIsCancellable = true;

                $orderMethodInstance = $order->getPayment()->getMethodInstance();
                $messageInterval = $interval;

                if(isset($orderMethodInstance->overridePendingTimeout)){
                    $messageInterval = $orderMethodInstance->overridePendingTimeout;
                    $intervalMethod = new \DateInterval("PT{$orderMethodInstance->overridePendingTimeout}M");
                    $cancellationTime = $date->sub($intervalMethod);
                    $orderDate = \DateTime::createFromFormat('Y-m-d H:i:s', $order->getCreatedAt());

                    if($orderDate > $cancellationTime){
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
                                    "Order canceled automatically by cron because order is pending since %1 minutes",
                                    $messageInterval
                                )
                            );

                        $order->save();
                    } catch (\Exception $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    public function getHipayMethods()
    {
        $methods = array();

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (strpos($code, 'hipay') !== false) {
                if ($this->_scopeConfig->getValue('payment/' . $code . "/cancel_pending_order")) {
                    $methods[] = $code;
                }
            }
        }

        return $methods;
    }

    public function getHostedHipayMethods()
    {
        $methods = array();

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (strpos($code, 'hipay') !== false && strpos($code, 'hipay_cc') === false) {
                if ($this->_scopeConfig->getValue('payment/' . $code . "/cancel_pending_order")) {
                    $methods[] = $code;
                }
            }
        }

        return $methods;
    }

}
