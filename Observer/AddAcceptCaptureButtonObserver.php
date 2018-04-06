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

namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * HiPay module observer
 * Add button "Accept and capture" in admin order view
 * When the order status is in pending review
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AddAcceptCaptureButtonObserver implements ObserverInterface
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Constructor
     *
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_coreRegistry = $registry;
        $this->buttonList = $context->getButtonList();
        $this->orderRepository = $orderRepository;
    }

    /**
     * Add accept and capture buuton to order view toolbar
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $controller = $observer->getControllerAction();
        if (($order = $this->getOrder($controller))) {
            if ((strpos($order->getPayment()->getMethod(), 'hipay') !== false)
                && $order->canReviewPayment()
            ) {

                /** @var $controller \Magento\Sales\Controller\Adminhtml\Order\View */
                $message = __('Are you sure you want to accept this payment?');
                $actionUrl = $controller->getUrl('hipay/order/acceptAndCapturePayment',
                    ['order_id' => $order->getEntityId()]);
                $this->buttonList->add('accept_capture_payment', [
                    'label' => __('Accept and Capture Payment'),
                    'onclick' => "confirmSetLocation('{$message}', '{$actionUrl}')",
                    "sort_order" => 10,
                    "class" => "primary"
                ]);
            } else {
                $this->buttonList->remove('accept_capture_payment');
            }
        }

        return $this;
    }


    /**
     * Retrieve order model object
     * @param \Magento\Sales\Controller\Adminhtml\Order\View $controller
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($controller)
    {
        if (is_null($this->order)) {

            $id = $controller->getRequest()->getParam('order_id');
            try {
                $this->order = $this->orderRepository->get($id);
                if ($this->order->getId()) {
                    $this->_coreRegistry->register('hipay_current_order', $this->order);
                }
            } catch (NoSuchEntityException $e) {
                return null;
            } catch (InputException $e) {
                return null;
            }
        }

        return $this->order;
    }
}
