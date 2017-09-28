<?php

namespace HiPay\FullserviceMagento\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

/**
 * Handler Request for gateway
 *
 *
 * @see  HiPay\FullserviceMagento\Model\Config.php
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class RequestHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = MonologLogger::DEBUG;

    /**
     *
     * @var string
     */
    protected $fileName = '/var/log/hipay.log';


}