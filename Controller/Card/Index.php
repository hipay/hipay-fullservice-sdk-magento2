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

namespace HiPay\FullserviceMagento\Controller\Card;

use HiPay\FullserviceMagento\Controller\Card\Customer as CustomerController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Card list
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Index extends CustomerController
{
    /**
     * Render my product reviews
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
 * @var \Magento\Framework\View\Result\Page $resultPage
*/
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('hipay/card');
        }

        if ($block = $resultPage->getLayout()->getBlock('card_customer_list')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $resultPage->getConfig()->getTitle()->set(__('My Credit Cards'));
        return $resultPage;
    }
}
