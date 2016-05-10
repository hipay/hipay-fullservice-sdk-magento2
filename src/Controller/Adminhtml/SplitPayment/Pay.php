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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\SplitPayment;

class Pay extends \Magento\Backend\App\Action
{

	/**
	 * {@inheritdoc}
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('HiPay_FullserviceMagento::split_pay');
	}

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('split_payment_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {

            try {
                // init model and delete
                $model = $this->_objectManager->create('HiPay\FullserviceMagento\Model\SplitPayment');
                $model->load($id);
                
                //Pay this split payment
                $model->pay();
                
                // display success message
                $this->messageManager->addSuccess(__('The split payment has been paid.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_splitpayment_on_pay',
                    ['id' => $id, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_splitpayment_on_pay',
                    ['id' => $id, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage() . " ({$e->getCode()})");
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['split_payment_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a split payment to pay.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
