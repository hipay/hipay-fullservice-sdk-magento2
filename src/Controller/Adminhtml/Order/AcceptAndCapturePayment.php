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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Controller to Accept and capture payment in pending review
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AcceptAndCapturePayment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Manage payment state
     *
     * Accept and capture a payment that is in "review" state
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
        	/** @var $order \Magento\Sales\Model\Order */
            $order = $this->_initOrder();
            if ($order) {
 				
            	//1. Authorize the payment
                $order->getPayment()->accept();
                /* @var $orderService \Magento\Sales\Model\Service\OrderService */
                $orderService = $this->_objectManager->create('Magento\Sales\Api\OrderManagementInterface');
                $orderService->setState($order, 
                						\Magento\Sales\Model\Order::STATE_PROCESSING,
                						\HiPay\FullserviceMagento\Model\Config::STATUS_AUTHORIZED, 
                						'',
        								null,false);

                $this->orderRepository->save($order);
                       
                $message = __('The payment has been authorized.');
                $this->messageManager->addSuccess($message);
                
                $order->getPayment()->getMethodInstance()->capture($order->getPayment(),$order->getBaseTotalDue());
                
                $message = __('The payment has been captured too.');
                $this->messageManager->addSuccess($message);
                
                $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
                
            } else {
                $resultRedirect->setPath('sales/*');
                return $resultRedirect;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update the payment right now.'));
            $this->logger->critical($e);
        }
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::review_payment');
    }
}
