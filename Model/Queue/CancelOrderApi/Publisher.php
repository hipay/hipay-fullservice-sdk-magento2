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

use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Queue publisher for HiPay notifications
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Publisher
{
    private const TOPIC_NAME = "order.cancel.hipay.api";

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PublisherInterface $publisher
     * @param LoggerInterface    $logger
     */
    public function __construct(PublisherInterface $publisher, LoggerInterface $logger)
    {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     *  Publish an order ID to the cancel order API queue topic
     *
     * @param string $id
     * @return void
     */
    public function execute(string $id)
    {
        if ($id) {
            $this->publisher->publish(self::TOPIC_NAME, $id);
        }
    }
}
