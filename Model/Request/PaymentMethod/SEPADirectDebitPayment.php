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

namespace HiPay\FullserviceMagento\Model\Request\PaymentMethod;

use HiPay\Fullservice\Gateway\Request\PaymentMethod\SEPADirectDebitPaymentMethod;

/**
 * SEPADirectDebitPaymentMethod Payment Method Request Object
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SEPADirectDebitPayment extends AbstractPaymentMethod
{
    /**
     *  Map Request for specific SEPADirectDebitPaymentMethod
     *
     * @return SddPaymentMethod
     */
    protected function mapRequest()
    {
        $sddPaymentMethod = new SEPADirectDebitPaymentMethod();
        $sddPaymentMethod->recurring_payment = 0;

        /**
        * @var HiPay\Fullservice\Gateway\Request\PaymentMethod\SEPADirectDebitPaymentMethod
        */
        $sddPaymentMethod->bank_name = $this->_order->getPayment()->getAdditionalInformation('sdd_bank_name');
        $sddPaymentMethod->issuer_bank_id = $this->_order->getPayment()->getAdditionalInformation('sdd_bic');
        $sddPaymentMethod->iban = $this->_order->getPayment()->getAdditionalInformation('sdd_iban');
        $sddPaymentMethod->firstname = $this->_order->getPayment()->getAdditionalInformation('sdd_firstname');
        $sddPaymentMethod->lastname = $this->_order->getPayment()->getAdditionalInformation('sdd_lastname');
        $sddPaymentMethod->gender = $this->_order->getPayment()->getAdditionalInformation('sdd_gender');

        return $sddPaymentMethod;
    }
}
