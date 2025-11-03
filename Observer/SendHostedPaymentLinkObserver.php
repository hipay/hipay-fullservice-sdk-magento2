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
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;

/**
 * HiPay module observer
 *
 * Send Hosted page link to the customer when order was created in Admin (payment Mo/To)
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
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @param HostedPaymentLinkSender $paymenLinkSender
     * @param ResponseFactory         $responseFactory
     * @param ResponseInterface       $response
     * @param ActionFlag              $actionFlag
     */
    public function __construct(
        HostedPaymentLinkSender $paymenLinkSender,
        ResponseFactory $responseFactory,
        ResponseInterface $response,
        ActionFlag $actionFlag
    ) {
        $this->paymenLinkSender = $paymenLinkSender;
        $this->responseFactory = $responseFactory;
        $this->response = $response;
        $this->actionFlag = $actionFlag;
    }

    /**
     * Send email with payment link to the customer
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
                $this->actionFlag->set('', 'no-dispatch', true);
                $response = $this->responseFactory->create();
                $response->setRedirect($url);
                $response->sendResponse();

                return $this;
            }

            $this->paymenLinkSender->send($order);
        }

        return $this;
    }
}
