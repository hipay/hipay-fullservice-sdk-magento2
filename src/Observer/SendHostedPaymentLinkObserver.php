<?php
/**
 * HiPay fullservice SDK
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
 */
class SendHostedPaymentLinkObserver implements ObserverInterface
{

	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender $paymenLinkSender;
	 */
	protected $paymenLinkSender;

    /**
     * Constructor
     *
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\Email\Sender\HostedPaymentLinkSender $paymenLinkSender
    ) {
        $this->paymenLinkSender = $paymenLinkSender;
    }

    /**
     * Send email with payment link to the customer
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        $url = $order->getPayment()->getAdditionalInformation('redirectUrl');
        
		if($url && (strpos($order->getPayment()->getMethod(),'hipay_hosted') !== false)){
			$this->paymenLinkSender->send($order);
		}
		
        return $this;
    }
}
