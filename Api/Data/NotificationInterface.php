<?php

namespace HiPay\FullserviceMagento\Api\Data;

interface NotificationInterface
{
    public const NOTIFICATION_ID = 'notification_id';

    public const STATUS = 'status';

    public const CONTENT = 'content';

    public const HIPAY_CREATED_AT = 'hipay_created_at';

    public const CREATED_AT = 'created_at';

    public const ATTEMPTS = 'attempts';

    public const STATE = 'state';

    /**
     * Gets Notification ID.
     *
     * @return int|null
     */
    public function getNotificationId();

    /**
     * Sets Notification ID.
     *
     * @param int $notificationId
     * @return $this
     */
    public function setNotificationId($notificationId);

    /**
     * Gets Status.
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Sets Status.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Gets Content.
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Sets Content.
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Gets Hipay Created At.
     *
     * @return \DateTime|null
     */
    public function getHipayCreatedAt();

    /**
     * Sets Hipay Created At.
     *
     * @param \DateTime $hipayCreatedAt
     * @return $this
     */
    public function setHipayCreatedAt($hipayCreatedAt);

    /**
     * Gets Created At.
     *
     * @return \DateTime|null
     */
    public function getCreatedAt();

    /**
     * Sets Created At.
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets Attempts.
     *
     * @return int|null
     */
    public function getAttempts();

    /**
     * Sets Attempts.
     *
     * @param int $attempts
     * @return $this
     */
    public function setAttempts($attempts);

    /**
     * Gets State.
     *
     * @return string|null
     */
    public function getState();

    /**
     * Sets State.
     *
     * @param string $state
     * @return $this
     */
    public function setState($state);
}
