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

namespace HiPay\FullserviceMagento\Observer;

use HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender;
use HiPay\FullserviceMagento\Model\HostedMotoRedirect;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;

/**
 * HiPay module observer
 *
 * When an order is created in Admin (MO/TO payment): either send the hosted
 * page link to the customer by email, or hand the hosted page URL to the
 * order-create controller plugin so it redirects the admin to HiPay.
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SendHostedPaymentLinkObserver implements ObserverInterface
{
    /**
     *
     * @var HostedPaymentLinkSender $paymenLinkSender ;
     */
    protected $paymenLinkSender;

    /**
     * @var HostedMotoRedirect
     */
    protected $hostedMotoRedirect;

    /**
     * @param HostedPaymentLinkSender $paymenLinkSender
     * @param HostedMotoRedirect      $hostedMotoRedirect
     */
    public function __construct(
        HostedPaymentLinkSender $paymenLinkSender,
        HostedMotoRedirect $hostedMotoRedirect
    ) {
        $this->paymenLinkSender = $paymenLinkSender;
        $this->hostedMotoRedirect = $hostedMotoRedirect;
    }

    /**
     * Send the payment link by email, or store the hosted page URL for redirect.
     *
     * @param  EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var $order Order
         */
        $order = $observer->getEvent()->getData('order');
        $url = $order->getPayment()->getAdditionalInformation('redirectUrl');

        if ($url && (strpos($order->getPayment()->getMethod(), 'hipay_hostedmoto') !== false)) {
            $methodInstance = $order->getPayment()->getData('method_instance');

            if ($methodInstance && !$methodInstance->isSendMailToCustomer()) {
                // Hand the URL to the order-create plugin, which returns the redirect.
                $this->hostedMotoRedirect->setUrl($url);

                return $this;
            }

            $this->paymenLinkSender->send($order);
        }

        return $this;
    }
}
