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

namespace HiPay\FullserviceMagento\Model\Method\Facilypay;

use HiPay\FullserviceMagento\Model\Method\Facilypay\AbstractFacilypay;

/**
 * Credit Long payment method
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CreditLong extends AbstractFacilypay
{
    public const HIPAY_METHOD_CODE = 'hipay_creditlong';

    /**
     * @var string
     */
    protected static $_technicalCode = 'credit-long';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;
}
