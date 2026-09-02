<?php

namespace HiPay\FullserviceMagento\Observer;

use HiPay\FullserviceMagento\Api\ResponseNotFoundOrderRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;
use HiPay\FullserviceMagento\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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

    /**
     * @var ResponseNotFoundOrderRepositoryInterface
     */
    protected $notFoundOrderRepository;

    public function __construct(
        Session                                  $checkoutSession,
        Config                                   $hipayConfig,
        ResponseNotFoundOrderRepositoryInterface $notFoundOrderRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();
        $this->hipayConfig = $hipayConfig;
        $this->hipayConfig->setStoreId($storeId);
        $this->notFoundOrderRepository = $notFoundOrderRepository;
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
            && !$this->notFoundOrderRepository->isPendingOrderExist($lastRealOrder->getIncrementId())
        ) {
            $this->checkoutSession->restoreQuote();
        }
        return true;
    }
}
