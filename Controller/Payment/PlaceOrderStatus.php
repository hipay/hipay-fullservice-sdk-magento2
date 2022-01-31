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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Controller\Payment;

use HiPay\Fullservice\Enum\Transaction\TransactionState;

/**
 * Place order status controller
 *
 * Returns data from last customer's order
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PlaceOrderStatus extends \HiPay\FullserviceMagento\Controller\Fullservice
{
    /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $resultJson = $this->resultJsonFactory->create();
            $order = $this->_getCheckoutSession()->getLastRealOrder();

            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We can\'t place the order.')
                );
            }

            $payment = $order->getPayment();
            $response = [
                'redirectUrl' => $payment->getAdditionalInformation('redirectUrl'),
                'statusOK' =>
                    in_array($payment->getAdditionalInformation('status'), [
                        TransactionState::COMPLETED,
                        TransactionState::FORWARDING,
                        TransactionState::PENDING
                    ]) ? true : false
            ];
            return $resultJson->setData($response);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->logger->addDebug($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the order.')
            );
        }
    }
}
