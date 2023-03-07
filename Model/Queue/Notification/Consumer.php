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

use HiPay\FullserviceMagento\Model\Notification;
use HiPay\FullserviceMagento\Model\Notification\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Logger\Monolog;

/**
 * Queue consumer for HiPay notifications
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
     * @var Factory
     */
    protected $notificationFactory;

    /**
     * @var Monolog
     */
    protected $logger;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Factory $notificationFactory,
        Monolog $logger
    ) {
        $this->objectManager = $objectManager;
        $this->notificationFactory = $notificationFactory;
        $this->logger = $logger;
        $this->logger->pushHandler(\HiPay\FullserviceMagento\Logger\HipayHandler::getInstance());
    }

    public function execute(string $id)
    {
        if ($id) {
            $this->logger->info('Consuming notification ID "' . $id . '"');

            /** @var Notification */
            $notification = $this->notificationFactory->create();
            $notification->load($id);

            try {
                /** @var \HiPay\FullserviceMagento\Model\Notify */
                $notify = $this->objectManager->create(
                    '\HiPay\FullserviceMagento\Model\Notify',
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
