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

namespace HiPay\FullserviceMagento\Plugin\Adminhtml;

use HiPay\FullserviceMagento\Model\HostedMotoRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\Save;

/**
 * Redirect the admin to the HiPay hosted page after a MO/TO order is placed.
 *
 * Replaces the legacy die()-based redirect in SendHostedPaymentLinkObserver:
 * the observer stores the hosted page URL, this after-plugin returns the
 * corresponding redirect result instead of the default order-view redirect.
 *
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class OrderCreateSavePlugin
{
    /**
     * @var HostedMotoRedirect
     */
    private $hostedMotoRedirect;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @param HostedMotoRedirect $hostedMotoRedirect
     * @param RedirectFactory    $resultRedirectFactory
     */
    public function __construct(
        HostedMotoRedirect $hostedMotoRedirect,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->hostedMotoRedirect = $hostedMotoRedirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Return the HiPay hosted page redirect when the placed order requires it.
     *
     * @param  Save             $subject
     * @param  ResultInterface  $result
     * @return ResultInterface
     */
    public function afterExecute(Save $subject, ResultInterface $result)
    {
        $url = $this->hostedMotoRedirect->getUrl();

        if ($url) {
            $this->hostedMotoRedirect->setUrl(null);
            return $this->resultRedirectFactory->create()->setUrl($url);
        }

        return $result;
    }
}
