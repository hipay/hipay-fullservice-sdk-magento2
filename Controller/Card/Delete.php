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

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use HiPay\FullserviceMagento\Controller\Card\Customer as CustomerController;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Delete registered card
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Delete extends CustomerController implements HttpPostActionInterface
{
    /**
     *
     * @var \HiPay\FullserviceMagento\Model\CardFactory $_cardFactory
     */
    protected $cardFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \HiPay\FullserviceMagento\Model\CardFactory $cardFactory
     * @param FormKeyValidator                            $formKeyValidator
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
        FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context, $customerSession);
        $this->cardFactory = $cardFactory;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Delete the customer card once the form key is valid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('hipay/card');
        }

        /**
         * @var $card \HiPay\FullserviceMagento\Model\Card
         */
        $card = $this->cardFactory->create();
        $card->load($this->getRequest()->getParam('id'));
        if ($card->getCustomerId() != $this->customerSession->getCustomerId()) {
            /**
             * @var \Magento\Framework\Controller\Result\Forward $resultForward
             */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        try {
            $card->delete();
            $this->messageManager->addSuccess(__('You deleted your credit card.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong deleting the credit card.'));
        }

        /**
 * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('hipay/card');
    }
}
