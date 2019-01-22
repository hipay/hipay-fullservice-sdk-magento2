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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\PaymentProfile;

use Magento\Backend\App\Action;

/**
 * Delete payment profile
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
     * @var \HiPay\FullserviceMagento\Model\PaymentProfile\Factory
     */
    private $paymentProfileFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \HiPay\FullserviceMagento\Model\PaymentProfile\Factory $paymentProfileFactory
     */
    public function __construct(
        Action\Context $context,
        \HiPay\FullserviceMagento\Model\PaymentProfile\Factory $paymentProfileFactory
    ) {
        $this->paymentProfileFactory = $paymentProfileFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('profile_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->paymentProfileFactory->create();
                $model->getResource()->load($model, $id);
                $title = $model->getName();
                $model->getResource()->delete($model);
                // display success message
                $this->messageManager->addSuccess(__('The payment profile has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_paymentprofile_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_paymentprofile_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['profile_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a payment profile to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
