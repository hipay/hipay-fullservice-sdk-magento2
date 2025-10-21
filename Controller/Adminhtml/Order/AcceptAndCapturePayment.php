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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Controller to Accept and capture payment in pending review
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AcceptAndCapturePayment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AcceptAndCapturePayment constructor.
     *
     * @param Context                  $context
     * @param Registry                 $coreRegistry
     * @param FileFactory              $fileFactory
     * @param InlineInterface          $translateInline
     * @param PageFactory              $resultPageFactory
     * @param JsonFactory              $resultJsonFactory
     * @param LayoutFactory            $resultLayoutFactory
     * @param RawFactory               $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface          $logger
     * @param Session                  $backendSession
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        Session $backendSession
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );

        $this->backendSession = $backendSession;
        $this->logger = $logger;
    }

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
            /**
             * @var $order Order
             **/
            $order = $this->_initOrder();

            if ($order) {
                $order->getPayment()->accept();

                $this->messageManager->addSuccessMessage(__('The payment has been authorized.'));

                $order->getPayment()->getMethodInstance()->capture(
                    $order->getPayment(),
                    $order->getBaseTotalDue()
                );

                $this->messageManager->addSuccessMessage(__('The payment has been captured too.'));

                $this->backendSession->getCommentText(true);
            } else {
                $resultRedirect->setPath('sales/*');
                return $resultRedirect;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update the payment right now.'));
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
