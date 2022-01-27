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

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Hosted Payment Method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedMethod extends FullserviceMethod
{
    const HIPAY_METHOD_CODE = 'hipay_hosted';

    /**
     * @var string
     */
    protected $_formBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Info';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

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

        $this->_setHostedUrl($order);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    protected function _setHostedUrl(\Magento\Sales\Model\Order $order)
    {
        if ($order->getPayment()->getAdditionalInformation('card_token') != "") {
            $this->place($order->getPayment());
        } else {
            //Create gateway manage with order data
            $gateway = $this->_gatewayManagerFactory->create($order);
            //Call fullservice api to get hosted page url
            $hppModel = $gateway->requestHostedPaymentPage();
            $order->getPayment()->setAdditionalInformation('redirectUrl', $hppModel->getForwardUrl());
        }
    }

    /**
     * Capture payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }

        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            if ($payment->getAuthorizationTransaction()) {  //Is not the first transaction
                $this->manualCapture($payment, $amount);
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(__('There was an error capturing the transaction: %1.', $e->getMessage()));
        }

        return $this;
    }
}
