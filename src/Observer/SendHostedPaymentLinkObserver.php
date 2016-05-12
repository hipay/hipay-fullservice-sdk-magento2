<?php
/**
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
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
