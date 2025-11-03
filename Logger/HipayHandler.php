<?php

namespace HiPay\FullserviceMagento\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Handler for HiPay
 *
 * @see HiPay\FullserviceMagento\Model\Config.php
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HipayHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/hipay.log';

    /**
     * Get Instance
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self(new \Magento\Framework\Filesystem\Driver\File());
    }
}
