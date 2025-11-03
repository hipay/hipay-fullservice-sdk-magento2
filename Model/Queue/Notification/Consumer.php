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

namespace HiPay\FullserviceMagento\Model\Queue\Notification;

use HiPay\FullserviceMagento\Logger\HipayHandler;
use HiPay\FullserviceMagento\Model\Notification;
use HiPay\FullserviceMagento\Model\Notification\Factory;
use HiPay\FullserviceMagento\Model\Notify;
use HiPay\FullserviceMagento\Model\NotifyFactory;
use Magento\Framework\Logger\Monolog;

/**
 * Queue consumer for HiPay notifications
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Consumer
{
    /**
     * @var Factory
     */
    protected $notificationFactory;

    /**
     * @var NotifyFactory
     */
    protected $notifyFactory;

    /**
     * @var Monolog
     */
    protected $logger;

    /**
     * @param Factory       $notificationFactory
     * @param NotifyFactory $notifyFactory
     * @param Monolog       $logger
     */
    public function __construct(
        Factory $notificationFactory,
        NotifyFactory $notifyFactory,
        Monolog $logger
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->notifyFactory = $notifyFactory;
        $this->logger = $logger;
        $this->logger->pushHandler(HipayHandler::getInstance());
    }

    /**
     * Consume and process a queued notification by ID, updating its state based on transaction result.
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function execute(string $id)
    {
        if ($id) {
            /** @var Notification */
            $notification = $this->notificationFactory->create();
            $notification->load($id);

            $this->logger->info(
                'Consuming notification ID "' . $id . '" related to status ' . $notification->getStatus()
            );

            try {
                /** @var Notify */
                $notify = $this->notifyFactory->create(
                    ['params' => ['response' => json_decode($notification->getContent(), true)]]
                );
                $notify->processTransaction();

                $notification
                    ->setState(Notification::NOTIFICATION_STATE_DONE)
                    ->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());

                $notification
                    ->setAttempts($notification->getAttempts() + 1)
                    ->setState(Notification::NOTIFICATION_STATE_FAILED)
                    ->save();

                throw $e;
            }
        }
    }
}
