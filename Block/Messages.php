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

namespace HiPay\FullserviceMagento\Block;

/**
 * Hipay Fullservice messages block
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Messages extends \Magento\Framework\View\Element\Messages
{

    /**
     *
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * Messages constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Customer\Model\Session\Proxy $customerSession,
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
