<?php

/**
 * HiPay fullservice Magento
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

namespace HiPay\FullserviceMagento\Controller\Payment;

use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Method\BancomatPayHostedFields;

class PendingStatus extends \HiPay\FullserviceMagento\Controller\Fullservice
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $declineUrl = $this->_url->getUrl('hipay/redirect/decline', ['_secure' => true]);

        try {
            $order = $this->_getCheckoutSession()->getLastRealOrder();

            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We can\'t place the order.')
                );
            }

            $payment = $order->getPayment();
            $methodCode = (string) $payment->getMethod();
            $orderStatus = (string) $order->getStatus();
            $orderState = (string) $order->getState();
            $pendingUrl = $this->_url->getUrl('hipay/redirect/pendingpolling', ['_secure' => true]);
            $successUrl = $this->_url->getUrl('hipay/redirect/accept', ['_secure' => true]);
            $refusedStatus = (string) $payment->getMethodInstance()->getConfigData('order_status_payment_refused');
            $canceledStatus = (string) $payment->getMethodInstance()->getConfigData('order_status_payment_canceled');
            $redirectUrl = $payment->getAdditionalInformation('redirectUrl') ?: $pendingUrl;
            $statusOK = false;

            if (
                $methodCode === BancomatPayHostedFields::HIPAY_METHOD_CODE
                && $orderStatus === Config::STATUS_AUTHORIZATION_REQUESTED
            ) {
                $redirectUrl = $successUrl;
                $statusOK = true;
            } elseif (
                in_array(
                    $orderState,
                    [
                        \Magento\Sales\Model\Order::STATE_PROCESSING,
                        \Magento\Sales\Model\Order::STATE_COMPLETE
                    ],
                    true
                )
            ) {
                $redirectUrl = $successUrl;
                $statusOK = true;
            } elseif (
                $orderState === \Magento\Sales\Model\Order::STATE_CANCELED
                || $orderStatus === $refusedStatus
                || $orderStatus === $canceledStatus
            ) {
                $redirectUrl = $declineUrl;
            } elseif (
                in_array(
                    $orderStatus,
                    [
                        Config::STATUS_AUTHORIZATION_REQUESTED,
                        Config::STATUS_AUTHORIZED_PENDING,
                        (string) $payment->getMethodInstance()->getConfigData('order_status')
                    ],
                    true
                )
                || in_array(
                    $payment->getAdditionalInformation('status'),
                    [
                        TransactionState::COMPLETED,
                        TransactionState::FORWARDING,
                        TransactionState::PENDING
                    ],
                    true
                )
            ) {
                $redirectUrl = $pendingUrl;
                $statusOK = true;
            }

            return $resultJson->setData([
                'redirectUrl' => $redirectUrl,
                'statusOK' => $statusOK
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
            return $resultJson->setData([
                'redirectUrl' => $declineUrl,
                'statusOK' => false
            ]);
        } catch (\Exception $e) {
            $this->logger->addDebug($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the order.')
            );
            return $resultJson->setData([
                'redirectUrl' => $declineUrl,
                'statusOK' => false
            ]);
        }
    }
}
