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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\SplitPayment;

/**
 * Delete split payment
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Delete extends \Magento\Backend\App\Action
{

	/**
	 * {@inheritdoc}
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('HiPay_FullserviceMagento::split_delete');
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
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('HiPay\FullserviceMagento\Model\SplitPayment');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The split payment has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_splitpayment_on_delete',
                    ['id' => $id, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_splitpayment_on_delete',
                    ['id' => $id, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['split_payment_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a split payment to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
