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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model;

use HiPay\FullserviceMagento\Api\Data\NotificationInterface;

/**
 * Hipay Notification data model
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Notification extends \Magento\Framework\Model\AbstractModel implements NotificationInterface
{
    public const NOTIFICATION_STATE_CREATED = 'created';
    public const NOTIFICATION_STATE_IN_PROGRESS = 'in_progress';
    public const NOTIFICATION_STATE_FAILED = 'failed';
    public const NOTIFICATION_STATE_DONE = 'done';

    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\HiPay\FullserviceMagento\Model\ResourceModel\Notification::class);
        $this->setIdFieldName('notification_id');
    }

    /**
     * Retrieve notification ID.
     *
     * @return array|int|mixed|null
     */
    public function getNotificationId()
    {
        return $this->getData('notification_id');
    }

    /**
     * Set notification ID.
     *
     * @param mixed $notificationId
     * @return $this|Notification
     */
    public function setNotificationId($notificationId)
    {
        $this->setData('notification_id', $notificationId);
        return $this;
    }

    /**
     * Retrieve notification status.
     *
     * @return array|mixed|string|null
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * Set notification status.
     *
     * @param mixed $status
     * @return $this|Notification
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }

    /**
     * Retrieve notification content.
     *
     * @return array|mixed|string|null
     */
    public function getContent()
    {
        return $this->getData('content');
    }

    /**
     * Set notification content.
     *
     * @param mixed $content
     * @return $this|Notification
     */
    public function setContent($content)
    {
        $this->setData('content', $content);
        return $this;
    }

    /**
     * Retrieve creation date.
     *
     * @return array|\DateTime|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Set creation date.
     *
     * @param mixed $createdAt
     * @return $this|Notification
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    /**
     * Retrieve HiPay creation date.
     *
     * @return array|\DateTime|mixed|null
     */
    public function getHiPayCreatedAt()
    {
        return $this->getData('hipay_created_at');
    }

    /**
     * Set HiPay creation date.
     *
     * @param mixed $hipayCreatedAt
     * @return $this|Notification
     */
    public function setHiPayCreatedAt($hipayCreatedAt)
    {
        $this->setData('hipay_created_at', $hipayCreatedAt);
        return $this;
    }

    /**
     * Retrieve number of attempts.
     *
     * @return array|int|mixed|null
     */
    public function getAttempts()
    {
        return $this->getData('attempts');
    }

    /**
     * Set number of attempts.
     *
     * @param mixed $attempts
     * @return $this|Notification
     */
    public function setAttempts($attempts)
    {
        $this->setData('attempts', $attempts);
        return $this;
    }

    /**
     * Retrieve notification state.
     *
     * @return array|mixed|string|null
     */
    public function getState()
    {
        return $this->getData('state');
    }

    /**
     * Set notification state.
     *
     * @param mixed $state
     * @return $this|Notification
     */
    public function setState($state)
    {
        $this->setData('state', $state);
        return $this;
    }
}
