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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\Redirect;

/**
 * Accept controller
 *
 * Used to redirect the customer when payment is accepted
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Accept extends \Magento\Backend\App\Action
{
    protected $orderFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $orderIncrementId = $this->getRequest()->getParam('orderid');
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);

        if ($this->_authorization->isAllowed('Magento_Sales::actions_view') && $order) {
            return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $order->getId()]);
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }
    }
}
