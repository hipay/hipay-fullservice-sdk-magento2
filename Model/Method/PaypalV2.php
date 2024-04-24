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

namespace HiPay\FullserviceMagento\Model\Method;

/**
 * Paypal V2 payment method
 *
 * @author    Hipay
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaypalV2 extends AbstractMethodAPI
{
    public const HIPAY_METHOD_CODE = 'hipay_paypalapiv2';

    /**
     * @var string
     */
    protected static $_technicalCode = 'paypal';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * @var int
     */
    public $overridePendingTimeout = 500;

    protected $_additionalInformationKeys = ['browser_info'];
}
