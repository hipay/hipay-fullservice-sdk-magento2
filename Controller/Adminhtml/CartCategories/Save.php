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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\CartCategories;

use HiPay\FullserviceMagento\Model\CartCategories\Factory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Save Mapping category
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Factory
     */
    private $cartCategoriesFactory;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * Save constructor.
     *
     * @param Context   $context
     * @param Factory   $cartCategoriesFactory
     * @param Session   $backendSession
     */
    public function __construct(
        Context $context,
        Factory $cartCategoriesFactory,
        Session $backendSession
    ) {
        $this->cartCategoriesFactory = $cartCategoriesFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->cartCategoriesFactory->create();
            $id = $this->getRequest()->getParam('mapping_id');
            if ($id) {
                $model->load($id);
            } else {
                $model->load($data['category_magento_id'], 'category_magento_id');
                if ($model->getId()) {
                    $this->messageManager->addErrorMessage(__('You have already done this mapping.'));
                    $this->_getSession()->setFormData($data);
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]
                    );
                }
            }

            $model->setData($data);
            $this->_eventManager->dispatch(
                'hipay_cartcategories_prepare_save',
                ['cartcategories' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved this mapping category.'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['mapping_id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the mapping.')
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('mapping_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
