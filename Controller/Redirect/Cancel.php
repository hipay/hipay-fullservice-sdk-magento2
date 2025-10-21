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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Generic;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Cancel controller
 *
 * Used to redirect the customer when payment is cancelled
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Cancel extends Fullservice
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
     * Cancel constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Session $checkoutSession
     * @param Generic $hipaySession
     * @param LoggerInterface $logger
     * @param Factory $gatewayManagerFactory
     * @param JsonFactory $resultJsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
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
        OrderManagementInterface        $orderManagement,
        Cart                            $cart
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
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
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * */
    public function execute()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();

        if ($lastOrderId) {
            /**
             * @var $order  \Magento\Sales\Model\Order
             */
            $order = $this->orderRepository->get($lastOrderId);
            if ($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {
                /**
                 * @var $cart Cart
                 */
                $cart = $this->cart;
                $items = $order->getItemsCollection();
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
}
