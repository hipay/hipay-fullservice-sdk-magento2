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

/**
 * HiPay module observer
 *
 * Send Hosted page link to the customer when order was created in Admin (payment Mo/To)
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SendHostedPaymentLinkObserver implements ObserverInterface
{
    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender $paymenLinkSender ;
     */
    protected $paymenLinkSender;

    protected $responseFactory;

    /**
     * SendHostedPaymentLinkObserver constructor.
     * @param \HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender $paymenLinkSender
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender $paymenLinkSender,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->paymenLinkSender = $paymenLinkSender;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Send email with payment link to the customer
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {

        /** @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        $url = $order->getPayment()->getAdditionalInformation('redirectUrl');

        if ($url && (strpos($order->getPayment()->getMethod(), 'hipay_hostedmoto') !== false)) {
            if (!$order->getPayment()->getData('method_instance')->isSendMailToCustomer()) {
                $this->responseFactory->create()->setRedirect($url)->sendResponse(); //Redirect to HiPay hosted page
                die(); //This will stop execution and redirect to specific page
            }
            $this->paymenLinkSender->send($order);
        }

        return $this;
    }
}
