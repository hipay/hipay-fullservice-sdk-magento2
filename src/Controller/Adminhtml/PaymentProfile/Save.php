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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\PaymentProfile;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{


    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }


    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
        	
        	if((int)$data['period_max_cycles'] < 1){
        		throw new \Magento\Framework\Exception\LocalizedException(__("Period max cycles is equals zero or negative "));
        	}
        	
        	if((int)$data['period_frequency'] < 1){
        		throw new \Magento\Framework\Exception\LocalizedException(__("Period frequency is equals zero or negative for Payment Profile ID: %s"));
        	}
        	
        	$data['payment_type'] = \HiPay\FullserviceMagento\Model\PaymentProfile::PAYMENT_TYPE_SPLIT;
        	
            $model = $this->_objectManager->create('HiPay\FullserviceMagento\Model\PaymentProfile');

            $id = $this->getRequest()->getParam('profile_id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'hipay_paymentprofile_prepare_save',
                ['paymentprofile' => $model, 'request' => $this->getRequest()]
            );


            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this payment profile.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['profile_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the payment profile.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('profile_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
