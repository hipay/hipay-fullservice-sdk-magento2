<?php

namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;
use HiPay\FullserviceMagento\Model\Config;

class RestoreBasketObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $hipayConfig;

    public function __construct(
        Session $checkoutSession,
        Config $hipayConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();
        $this->hipayConfig = $hipayConfig;
        $this->hipayConfig->setStoreId($storeId);
    }

    public function execute(EventObserver $observer)
    {

        $lastRealOrder = $this->checkoutSession->getLastRealOrder();

        if (
            $lastRealOrder->getPayment()
            && $lastRealOrder->getPayment()->getMethodInstance()->getConfigData('restore_cart_on_back')
            && !$this->hipayConfig->isNotificationCronActive()
            && $lastRealOrder->getData('state') === 'pending_payment'
            && $lastRealOrder->getData('status') === 'pending_payment'
        ) {
            $this->checkoutSession->restoreQuote();
        }
        return true;
    }
}
