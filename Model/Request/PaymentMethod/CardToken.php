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

use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;
use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * CardToken Payment Method Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CardToken extends AbstractPaymentMethod
{
    protected function mapRequest()
    {
        //Token can be empty
        $cardtoken = $this->_order->getForcedCardToken() ?:
            $this->_order->getPayment()->getAdditionalInformation('card_token');
        $eci = $this->_order->getForcedEci() ?:
            ($this->_config->getValue('send_mail_to_customer') ? ECI::SECURE_ECOMMERCE :
            $this->_order->getPayment()->getAdditionalInformation('eci'));
        $authentication_indicator = $this->_order->getForcedAuthenticationIndicator() ?: $this->_helper->is3dSecure(
            $this->_config->getValue('authentication_indicator'),
            $this->_config->getValue('config_3ds_rules'),
            $this->getQuote()
        );

        $cardTokenPaymentMethod = new CardTokenPaymentMethod();
        $cardTokenPaymentMethod->authentication_indicator = $authentication_indicator;
        $cardTokenPaymentMethod->cardtoken = $cardtoken;
        $cardTokenPaymentMethod->eci = $eci ?: ECI::SECURE_ECOMMERCE;

        return $cardTokenPaymentMethod;
    }
}
