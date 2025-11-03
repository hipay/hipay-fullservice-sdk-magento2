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

namespace HiPay\FullserviceMagento\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Hipay Fullservice messages block
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Messages extends \Magento\Framework\View\Element\Messages
{
    /**
     *
     * @var Session $customerSession
     */
    protected $customerSession;

    /**
     * Messages constructor.
     *
     * @param Context                         $context
     * @param Factory                         $messageFactory
     * @param CollectionFactory               $collectionFactory
     * @param ManagerInterface                $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param Session                         $customerSession
     * @param array                           $data
     */
    public function __construct(
        Context $context,
        Factory $messageFactory,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
        $this->customerSession = $customerSession;
    }

    /**
     * Prepare layout and display HiPay MOTO transaction messages.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->customerSession->getFromMoto()) {
            $this->validateMoto();

            $this->addMessages($this->messageManager->getMessages(true));
            $this->customerSession->unsFromMoto();
        }
        return parent::_prepareLayout();
    }

    /**
     * Display success or error messages for MOTO transactions.
     *
     * @return void
     */
    protected function validateMoto()
    {

        if ($this->customerSession->getAccept()) {
            $this->messageManager->addSuccess(
                __('Thank you for your order. You will receveive a confirmation email soon.')
            );
            $message = __('You can check the status of your order by logging into your account.');
            if ($this->customerSession->isLoggedIn()) {
                $message = __('You can check the status of your order in your order history.');
            }
            $this->messageManager->addSuccess($message);
            $this->customerSession->unsAccept();
        } elseif ($this->customerSession->getDecline()) {
            $this->messageManager->addError(__('Your transaction is declined.'));
            $message = __('You can retry your order with another credit card.');
            $this->messageManager->addError($message);
            $this->customerSession->unsDecline();
        }
    }
}
