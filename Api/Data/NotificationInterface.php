<?php

namespace HiPay\FullserviceMagento\Api\Data;

interface NotificationInterface
{
    const NOTIFICATION_ID = 'notification_id';

    const STATUS = 'status';

    const CONTENT = 'content';

    const HIPAY_CREATED_AT = 'hipay_created_at';

    const CREATED_AT = 'created_at';

    const ATTEMPTS = 'attempts';

    const STATE = 'state';

    /**
     * Gets Notification ID
     * @return int|null
     */
    public function getNotificationId();

    /**
     * Sets Notification ID
     * @return $this
     */
    public function setNotificationId($notificationId);

    /**
     * Gets Status
     * @return string|null
     */
    public function getStatus();

    /**
     * Sets Status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Gets Content
     * @return string|null
     */
    public function getContent();

    /**
     * Sets Content
     * @return $this
     */
    public function setContent($content);

    /**
     * Gets Hipay Created At
     * @return \DateTime|null
     */
    public function getHipayCreatedAt();

    /**
     * Sets Hipay Created At
     * @return $this
     */
    public function setHipayCreatedAt($hipayCreatedAt);

    /**
     * Gets Created At
     * @return \DateTime|null
     */
    public function getCreatedAt();

    /**
     * Sets Created At
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets Attempts
     * @return int|null
     */
    public function getAttempts();

    /**
     * Sets Attempts
     * @return $this
     */
    public function setAttempts($attempts);

    /**
     * Gets State
     * @return string|null
     */
    public function getState();

    /**
     * Sets State
     * @return $this
     */
    public function setState($state);
}
