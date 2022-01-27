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

namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayFactory;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * HiPay module observer
 *
 * Check http signature from TPP notification
 *
 * Redirections haven't checked because http params can be not present (Depend of TPP config)
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

/**
 * HiPay module observer
 *
 * Add invoice to payment info on capture to send real amount to the gateway (multi-currency)
 *
 * @package HiPay\FullserviceMagento\Observer
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SalesOrderPaymentCaptureObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        $payment = $observer->getPayment();
        $invoice = $observer->getInvoice();

        $payment->setTransactionAdditionalInfo('invoice_capture', $invoice);
    }
}
