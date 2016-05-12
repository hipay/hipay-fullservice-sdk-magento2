<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Controller\Card;

use Magento\Framework\App\Action\Context;
use HiPay\FullserviceMagento\Controller\Card\Customer as CustomerController;
use Magento\Customer\Model\Session as CustomerSession;
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
        	$this->messageManager->addException($e, __('Something went wrong deleting the credit card.'));
        }
        
       /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
       
        return $resultRedirect->setPath('hipay/card');
    }
}
