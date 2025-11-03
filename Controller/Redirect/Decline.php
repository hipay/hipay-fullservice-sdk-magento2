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

namespace HiPay\FullserviceMagento\Controller\Redirect;

use HiPay\FullserviceMagento\Controller\Fullservice;
use HiPay\FullserviceMagento\Model\Gateway\Factory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Generic;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Decline controller
 *
 * Used to redirect the customer when payment is declined
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Decline extends Fullservice
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Session $checkoutSession
     * @param Generic $hipaySession
     * @param LoggerInterface $logger
     * @param Factory $gatewayManagerFactory
     * @param JsonFactory $resultJsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Cart $cart
     */
    public function __construct(
        Context                         $context,
        \Magento\Customer\Model\Session $customerSession,
        Session                         $checkoutSession,
        Generic                         $hipaySession,
        LoggerInterface                 $logger,
        Factory                         $gatewayManagerFactory,
        JsonFactory                     $resultJsonFactory,
        OrderRepositoryInterface        $orderRepository,
        Cart                            $cart
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;

        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $hipaySession,
            $logger,
            $gatewayManagerFactory,
            $resultJsonFactory
        );
    }

    /**
     * Handle declined payment: optionally re-add items to cart and redirect customer to failure page.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();

        if ($lastOrderId) {
            /**
             * @var $order  Order
             */
            $order = $this->orderRepository->get($lastOrderId);

            if ($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {
                /**
                 * @var $cart Cart
                 */
                $cart = $this->cart;
                $items = $order->getAllVisibleItems();

                try {
                    foreach ($items as $item) {
                        $cart->addOrderItem($item);
                    }

                    $cart->save();
                } catch (LocalizedException $e) {
                    if ($this->checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNoticeMessage($e->getMessage());
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                }
            }
        }
        //MO/TO case
        if ($this->getRequest()->getParam('is_moto', false)) {
            $this->_customerSession->setFromMoto(true);
            $this->_customerSession->setDecline(true);
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }

        $this->checkoutSession->setErrorMessage(__('Your order was declined.'));
        $this->_redirect('checkout/onepage/failure');
    }
}
