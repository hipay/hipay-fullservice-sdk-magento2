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

use Magento\Backend\App\Action;

/**
 * Save split payment
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \HiPay\FullserviceMagento\Model\SplitPayment\Factory
     */
    private $splitPaymentFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \HiPay\FullserviceMagento\Model\SplitPayment\Factory $splitPaymentFactory
     */
    public function __construct(
        Action\Context $context,
        \HiPay\FullserviceMagento\Model\SplitPayment\Factory $splitPaymentFactory
    ) {
        $this->splitPaymentFactory = $splitPaymentFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('HiPay_FullserviceMagento::split_save');
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
            $model = $this->splitPaymentFactory->create();

            $id = $this->getRequest()->getParam('split_payment_id');
            if ($id) {
                $model->getResource()->load($model, $id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'hipay_splitpayment_prepare_save',
                ['splitpayment' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->getResource()->save($model);
                $this->messageManager->addSuccess(__('You saved this split payment.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['split_payment_id' => $model->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the split payment.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['split_payment_id' => $this->getRequest()->getParam('split_payment_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
