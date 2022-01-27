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

namespace HiPay\FullserviceMagento\Model\Request\PaymentMethod;

use HiPay\Fullservice\Gateway\Request\PaymentMethod\IDealPaymentMethod;

/**
 * IDeal Payment Method Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class IDeal extends AbstractPaymentMethod
{
    protected function mapRequest()
    {
        $iDealPaymentMethod = new IDealPaymentMethod();
        $iDealPaymentMethod->issuer_bank_id = $this->_order->getPayment()->getAdditionalInformation('issuer_bank_id');

        return $iDealPaymentMethod;
    }
}
