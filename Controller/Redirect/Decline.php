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
namespace HiPay\FullserviceMagento\Controller\Redirect;

use HiPay\FullserviceMagento\Controller\Fullservice;

/**
 * Decline controller
 *
 * Used to redirect the customer when payment is declined
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Decline extends Fullservice
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * Decline constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\Generic $hipaySession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Framework\Session\Generic $hipaySession,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
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
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {

        $lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
        if ($lastOrderId) {
            /** @var $order  \Magento\Sales\Model\Order **/
            $order = $this->orderFactory->create();
            $order->getResource()->load($order, $lastOrderId);
            if ($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {
                /** @var $cart \Magento\Checkout\Model\Cart **/
                $cart  = $this->_objectManager->get('Magento\Checkout\Model\Cart');
                $items = $order->getItemsCollection();
                try {
                    foreach ($items as $item) {
                        $cart->addOrderItem($item);
                    }

                    $cart->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException(
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

        $this->_checkoutSession->setErrorMessage(__('Your order was declined.'));
        $this->_redirect('checkout/onepage/failure');
    }
}
