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

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use HiPay\FullserviceMagento\Model\Notification;
use HiPay\FullserviceMagento\Model\ResourceModel\Notification\Collection;
use HiPay\FullserviceMagento\Model\Queue\Notification\Publisher;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Framework\Logger\Monolog;

/**
 * HiPay module crontab
 *
 * Used to process notifications via background process
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ProcessNotifications
{
    /**
     * @var Collection
     */
    protected $notificationCollection;

    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @var Monolog
     */
    protected $logger;

    /**
     * @var ResourceOrder $orderResource
     */
    protected $orderResource;

    public function __construct(
        Collection $notificationCollection,
        Publisher $publisher,
        ResourceOrder $orderResource,
        Monolog $logger
    ) {
        $this->notificationCollection = $notificationCollection;
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->logger->pushHandler(\HiPay\FullserviceMagento\Logger\HipayHandler::getInstance());
        $this->orderResource = $orderResource;
    }

    public function execute()
    {
        // Order of treatment
        $notificationOrderGroups = [
            [
                // In progress
                TransactionStatus::AUTHORIZED_AND_PENDING,
                TransactionStatus::AUTHORIZATION_REQUESTED,
                144, // Reference rendered
                169, // Credit requested
                172, // In progress
                174, // Awaiting Terminal
                177, // Challenge requested
                200, // Pending Payment
            ],
            [
                // Failed Status
                TransactionStatus::AUTHENTICATION_FAILED,
                TransactionStatus::BLOCKED,
                TransactionStatus::DENIED,
                TransactionStatus::REFUSED,
                TransactionStatus::EXPIRED,
                134, // Dispute lost
                178, // Soft decline
            ],
            [TransactionStatus::CHARGED_BACK],
            [TransactionStatus::AUTHORIZED],
            [
                // Capture requested
                TransactionStatus::CAPTURE_REQUESTED,
                TransactionStatus::CAPTURE_REFUSED,
            ],
            [TransactionStatus::PARTIALLY_CAPTURED],
            [
                // Paid
                TransactionStatus::CAPTURED,
                166, // Debited (cardholder credit)
                168, // Debited (cardholder credit)
            ],
            [   // Refund requested
                TransactionStatus::REFUND_REQUESTED,
                TransactionStatus::REFUND_REFUSED,
            ],
            [TransactionStatus::PARTIALLY_REFUNDED],
            [TransactionStatus::REFUNDED],
            [
                TransactionStatus::CANCELLED,
                143, // Authorization cancelled
                TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED,
            ],
        ];

        $cases = 'CASE ';
        foreach ($notificationOrderGroups as $position => $group) {
            $cases .= ' WHEN status IN (' . implode(', ', $group) . ') THEN ' . ($position + 1);
        }
        $cases .= ' ELSE ' . (count($notificationOrderGroups) + 1) . ' END';

        $notifications = $this->notificationCollection
            ->addFieldToFilter('state', ['in' => [
                Notification::NOTIFICATION_STATE_CREATED,
                Notification::NOTIFICATION_STATE_FAILED,
                Notification::NOTIFICATION_STATE_IN_PROGRESS
            ]])
            ->addFieldToFilter('attempts', ['lt' => 50]);

        $notifications->getSelect()->order(new \Zend_Db_Expr($cases));
        $notifications = $notifications->load()->getItems();

        // Inject notifications in progress in array if exists since 1 day
        $yesterday = new \DateTime('- 1 day');
        $notifications = array_filter($notifications, function (Notification $notification) use ($yesterday) {
            if (
                $notification->getState() !== Notification::NOTIFICATION_STATE_IN_PROGRESS
                || $notification->getCreatedAt() < $yesterday
            ) {
                return true;
            }
        });

        // Nothing to process: skip and avoid opening a transaction.
        if (!$notifications) {
            return;
        }

        $this->notificationCollection
            ->setDataToAll('state', Notification::NOTIFICATION_STATE_IN_PROGRESS)
            ->save();

        /** @var Notification[] $notifications */
        foreach ($notifications as $notification) {
            $this->publisher->execute($notification->getId());
        }

        $this->logger->info('Processing ' . count($notifications) . ' HiPay notifications');

        // We commit in the case of an open transaction
        try {
            $this->orderResource->getConnection()->query('commit');
        } catch (\Exception $e) {
            $this->logger->info('Error during commit : ' . $e);
        }
    }
}
