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

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Logger\Monolog;
use Magento\Sales\Api\OrderRepositoryInterface;
use HiPay\FullserviceMagento\Model\Gateway\Factory as ManagerFactory;
use HiPay\FullserviceMagento\Model\Queue\CancelOrderApi\Publisher as CancelOrderApiPublisher;

/**
 * Queue consumer for HiPay Cancel Order Api
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Consumer
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
     * @param ObjectManagerInterface $objectManager
     * @param Monolog $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerFactory $gatewayManagerFactory
     * @param Publisher $cancelOrderApiPublisher
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Monolog $logger,
        OrderRepositoryInterface $orderRepository,
        ManagerFactory $gatewayManagerFactory,
        CancelOrderApiPublisher $cancelOrderApiPublisher
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->logger->pushHandler(\HiPay\FullserviceMagento\Logger\HipayHandler::getInstance());
        $this->orderRepository = $orderRepository;
        $this->gatewayManagerFactory = $gatewayManagerFactory;
        $this->cancelOrderApiPublisher = $cancelOrderApiPublisher;
    }

    /**
     * @param string $id
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $id)
    {
        if ($id) {


            $this->logger->info(
                'Consuming cancel order ID "' . $id
            );

            try {
                /** @var $order */
                $order = $this->orderRepository->get((int) $id);

                if (!empty($order)) {
                  $gateway = $this->gatewayManagerFactory->create($order);
                  $gateway->requestOperationCancel();
                }
            } catch (\Exception $e) {
                $this->cancelOrderApiPublisher->execute((string) $id);
                $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());

                throw $e;
            }
        }
    }
}
