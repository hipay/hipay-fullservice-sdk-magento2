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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\CartCategories;

use Magento\Backend\App\Action;

/**
 * Save Mapping category
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \HiPay\FullserviceMagento\Model\CartCategories\Factory
     */
    private $cartCategoriesFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \HiPay\FullserviceMagento\Model\CartCategories\Factory $cartCategoriesFactory
     */
    public function __construct(
        Action\Context $context,
        \HiPay\FullserviceMagento\Model\CartCategories\Factory $cartCategoriesFactory
    ) {
        $this->cartCategoriesFactory = $cartCategoriesFactory;
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
            $model = $this->cartCategoriesFactory->create();
            $id = $this->getRequest()->getParam('mapping_id');
            if ($id) {
                $model->getResource()->load($model, $id);
            } else {
                $model->getResource()->load($model, $data['category_magento_id'], 'category_magento_id');
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
                $model->getResource()->save($model);
                $this->messageManager->addSuccess(__('You saved this mapping category.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['mapping_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the mapping.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('mapping_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
