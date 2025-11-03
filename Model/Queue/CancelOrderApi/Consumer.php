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

namespace HiPay\FullserviceMagento\Model\Queue\CancelOrderApi;

use HiPay\FullserviceMagento\Logger\HipayHandler;
use Magento\Framework\Logger\Monolog;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use HiPay\FullserviceMagento\Model\Queue\CancelOrderApi\Publisher as CancelOrderApiPublisher;

/**
 * Queue consumer for HiPay Cancel Order Api
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Consumer
{
    /**
     * @var Monolog
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     *
     * @var ManagerFactory $gatewayManagerFactory
     */
    protected $gatewayManagerFactory;

    /**
     * @var CancelOrderApiPublisher
     */
    protected $cancelOrderApiPublisher;

    /**
     * @param Monolog                  $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerFactory           $gatewayManagerFactory
     * @param Publisher                $cancelOrderApiPublisher
     */
    public function __construct(
        Monolog $logger,
        OrderRepositoryInterface $orderRepository,
        ManagerFactory $gatewayManagerFactory,
        CancelOrderApiPublisher $cancelOrderApiPublisher
    ) {
        $this->logger = $logger;
        $this->logger->pushHandler(HipayHandler::getInstance());
        $this->orderRepository = $orderRepository;
        $this->gatewayManagerFactory = $gatewayManagerFactory;
        $this->cancelOrderApiPublisher = $cancelOrderApiPublisher;
    }

    /**
     * Cancel an order by ID using the gateway API
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function execute(string $id)
    {
        if ($id) {
            $this->logger->info(
                'Consuming cancel order ID "' . $id
            );

            try {
                /**
                 * @var $order OrderInterface
                 */
                $order = $this->orderRepository->get((int) $id);

                if (!empty($order)) {
                    $gateway = $this->gatewayManagerFactory->create($order);
                    $gateway->requestOperationCancel();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());

                throw $e;
            }
        }
    }
}
