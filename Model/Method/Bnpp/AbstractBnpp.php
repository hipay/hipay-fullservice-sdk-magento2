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
namespace HiPay\FullserviceMagento\Model\Method\Bnpp;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;

class AbstractBnpp extends AbstractMethodAPI
{
    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        /*
        * calling parent validate function
        */
        parent::validate();
        $paymentInfo = $this->getInfoInstance();

        $order = $paymentInfo->getQuote();
        if ($paymentInfo->getOrder()) {
            $order = $paymentInfo->getOrder();
        }

        $phone = $order->getBillingAddress()->getTelephone();
        if (!preg_match('/(0|\+?33|0033)[1-9][0-9]{8}/', $phone)) {
            throw new \Magento\Framework\Exception\LocalizedException('Please check the phone number entered.');
        }

        return $this;
    }
}
