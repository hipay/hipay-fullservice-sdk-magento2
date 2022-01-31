<?php

/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Notification Helper class
 *
 * @author Hipay
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Notification extends AbstractHelper
{
    /**
     * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\CollectionFactory $inboxFactory
     */
    private $_inboxFactory;

    /**
     * @var \Magento\Customer\Model\Session $session
     */
    private $_session;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\AdminNotification\Model\ResourceModel\Inbox\CollectionFactory $inboxFactory
    ) {
        parent::__construct($context);
        $this->_session = $session;
        $this->_inboxFactory = $inboxFactory;
    }

    /**
     * Returns boolean on whether the notification has already been added to the inbox or not
     * @param $data Notification data
     * @return bool
     */
    public function isNotificationAlreadyAdded($data)
    {
        /**
         * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection $notificationCollection
         */
        $notificationCollection = $this->_inboxFactory->create();
        $notificationCollection->addFieldToSelect(['notification_id']);
        $notificationCollection->addFieldToFilter('url', array("eq" => $data['url']));

        return $notificationCollection->count() > 0;
    }

    public function isNotificationAlreadyRead($data)
    {
        /**
         * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection $notificationCollection
         */
        $notificationCollection = $this->_inboxFactory->create();
        $notificationCollection->addFieldToSelect(['notification_id']);
        $notificationCollection->addFieldToFilter('url', array("eq" => $data['url']));
        $notificationCollection->addFieldToFilter(
            array(
                'is_read',
                'is_remove'
            ),
            array(
                array("eq" => 1),
                array("eq" => 1)
            )
        );

        return $notificationCollection->count() > 0;
    }
}
