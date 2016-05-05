<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Customer;

use Magento\Review\Controller\Customer as CustomerController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\Controller\ResultFactory;

class Delete extends CustomerController
{

	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\CardFactory $_cardFactory
	 */
	protected $cardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
    	\HiPay\FullserviceMagento\Model\CardFactory $cardFactory
    ) {
        parent::__construct($context, $customerSession);
        $this->cardFactory = $cardFactory;
    }
    /**
     * Render review details
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	/** @var $card \HiPay\FullserviceMagento\Model\Card */
        $card = $this->cardFactory->create()->load($this->getRequest()->getParam('id'));
        if ($card->getCustomerId() != $this->customerSession->getCustomerId()) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
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
        	$this->messageManager->addException($e, __('Something went wrong  deleteing the credit card.'));
        }
        
       /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
       
        return $resultRedirect->setPath('hipay/card');
    }
}
