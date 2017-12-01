<?php
/**
 * Created by PhpStorm.
 * User: aberthelot
 * Date: 30/11/17
 * Time: 10:49
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
        if (!preg_match('"(0|\\+33|0033)[1-9][0-9]{8}"', $phone)) {
            throw new \Magento\Framework\Exception\LocalizedException('Please check the phone number entered.');
        }

        return $this;
    }

}