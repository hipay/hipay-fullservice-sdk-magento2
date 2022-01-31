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

namespace HiPay\FullserviceMagento\Model\Method;

/**
 * Local Hosted Fields Model payment method
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class LocalHostedFields extends FullserviceMethod
{
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

        $this->processAction($paymentAction, $payment);

        $stateObject->setIsNotified(false);
    }

    /**
     * Perform actions based on passed action name
     *
     * @param string $action
     * @param Magento\Payment\Model\InfoInterface $payment
     * @return void
     */
    protected function processAction($action, $payment)
    {
        $totalDue = $payment->getOrder()->getTotalDue();
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();

        switch ($action) {
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_AUTH:
                $this->authorize($payment, $baseTotalDue);
                // base amount will be set inside
                $payment->setAmountAuthorized($totalDue);
                break;
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_SALE:
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $this->capture($payment, $payment->getOrder()->getBaseGrandTotal());
                break;
            default:
                break;
        }
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);
        $this->place($payment);
        return $this;
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject $additionalData
     * @return $this
     * @throws LocalizedException
     */
    public function _assignAdditionalInformation(\Magento\Framework\DataObject $additionalData)
    {
        parent::_assignAdditionalInformation($additionalData);
        $info = $this->getInfoInstance();
        $info->setCcType($additionalData->getCcType());

        return $this;
    }
}
