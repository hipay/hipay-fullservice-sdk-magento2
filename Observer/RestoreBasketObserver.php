<?php

namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;

class RestoreBasketObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(EventObserver $observer)
    {

        $lastRealOrder = $this->checkoutSession->getLastRealOrder();

        if (
            $lastRealOrder->getPayment()
            && $lastRealOrder->getPayment()->getMethodInstance()->getConfigData('restore_cart_on_back')
            && $lastRealOrder->getData('state') === 'pending_payment'
            && $lastRealOrder->getData('status') === 'pending_payment'
        ) {
            $this->checkoutSession->restoreQuote();
        }
        return true;
    }
}
