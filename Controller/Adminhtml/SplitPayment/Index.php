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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\SplitPayment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Split payment list
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Split payment Grid
     * {@inheritDoc}
     *
     * @see    \Magento\Framework\App\ActionInterface::execute()
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('HiPay_FullserviceMagento::hipay_split_payment');
        $resultPage->addBreadcrumb(__('HiPay'), __('HiPay'));
        $resultPage->addBreadcrumb(__('Split Payments'), __('Split Payments'));

        $resultPage->getConfig()->getTitle()->prepend(__('Split Payments'));

        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('HiPay_FullserviceMagento::hipay_split_payment');
    }
}
