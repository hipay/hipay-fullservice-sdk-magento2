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

use HiPay\FullserviceMagento\Model\Notification;
use HiPay\FullserviceMagento\Model\ResourceModel\Notification\Collection;
use HiPay\FullserviceMagento\Model\Queue\Notification\Publisher;
use HiPay\FullserviceMagento\Model\Config;
use Magento\Checkout\Model\Session;
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
     * @var Config
     */
    protected $_hipayConfig;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    public function __construct(
        Collection $notificationCollection,
        Publisher $publisher,
        Config $hipayConfig,
        Session $checkoutSession,
        Monolog $logger
    ) {
        $this->notificationCollection = $notificationCollection;
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->logger->pushHandler(\HiPay\FullserviceMagento\Logger\HipayHandler::getInstance());
        $this->_checkoutSession = $checkoutSession;
        $storeId = $this->_checkoutSession->getQuote()->getStore()->getStoreId();
        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);
    }

    public function execute()
    {
        $cronModeActivated = $this->_hipayConfig->isNotificationCronActive();

        if($cronModeActivated){
            $notifications = $this->notificationCollection
                ->addFieldToFilter('state', ['in' => [
                    Notification::NOTIFICATION_STATE_CREATED,
                    Notification::NOTIFICATION_STATE_FAILED,
                    Notification::NOTIFICATION_STATE_IN_PROGRESS
                ]])
                ->addFieldToFilter('attempts', ['lt' => 50])
                ->addOrder('status', 'asc')
                ->load()
                ->getItems();

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
            
            if (count($notifications)) {
                $this->notificationCollection
                    ->setDataToAll('state', Notification::NOTIFICATION_STATE_IN_PROGRESS)
                    ->save();
            }
            
            foreach ($notifications as $notification) {
                $this->publisher->execute($notification->getId());
            }

            $this->logger->info('Processing ' . count($notifications) . ' HiPay notifications');
        }else{
            $this->logger->info('Cron notifications disabled');
        }
    }
}