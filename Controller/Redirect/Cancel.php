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
use HiPay\FullserviceMagento\Model\RedirectToken;
use HiPay\FullserviceMagento\Model\Gateway\Factory;
use HiPay\FullserviceMagento\Model\Method\FullserviceMethod;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Cancel controller
 *
 * Used to redirect the customer when payment is cancelled
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Cancel extends Fullservice implements HttpGetActionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var RedirectToken
     */
    private $redirectToken;

    /**
     * Cancel constructor.
     *
     * @param Context                         $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Session                         $checkoutSession
     * @param Generic                         $hipaySession
     * @param LoggerInterface                 $logger
     * @param Factory                         $gatewayManagerFactory
     * @param JsonFactory                     $resultJsonFactory
     * @param OrderRepositoryInterface        $orderRepository
     * @param OrderManagementInterface        $orderManagement
     * @param Cart                            $cart
     * @param RedirectToken                   $redirectToken
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
        OrderManagementInterface        $orderManagement,
        Cart                            $cart,
        RedirectToken                   $redirectToken
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->redirectToken = $redirectToken;

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
     * Cancel the last order when the customer cancels the payment on HiPay
     *
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * */
    public function execute()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();

        if ($lastOrderId) {
            try {
                /**
                 * @var $order  \Magento\Sales\Model\Order
                 */
                $order = $this->orderRepository->get($lastOrderId);
            } catch (NoSuchEntityException $e) {
                $this->_redirect('checkout/cart');
                return;
            }

            if (!$this->canCancel($order)) {
                $this->logger->warning(
                    'HiPay cancel redirect refused.',
                    ['order_id' => $order->getIncrementId(), 'state' => $order->getState()]
                );
                $this->_redirect('checkout/cart');
                return;
            }

            if ((bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {
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

            $this->orderManagement->cancel($lastOrderId);
            $this->messageManager->addNoticeMessage(
                __('Your order #%1 was canceled.', $order->getIncrementId())
            );
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Only a pending HiPay order with a valid cancel token can be canceled
     *
     * @param  Order $order
     * @return bool
     */
    private function canCancel(Order $order)
    {
        return $this->redirectToken->isValid($order, $this->getRequest()->getParam(RedirectToken::PARAM))
            && in_array($order->getState(), [Order::STATE_NEW, Order::STATE_PENDING_PAYMENT], true)
            && $order->canCancel()
            && $order->getPayment()->getMethodInstance() instanceof FullserviceMethod;
    }
}
