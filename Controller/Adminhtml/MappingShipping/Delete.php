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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\MappingShipping;

use Magento\Backend\App\Action;

/**
 * Delete payment profile
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \HiPay\FullserviceMagento\Model\MappingShipping\Factory
     */
    private $mappingShippingFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \HiPay\FullserviceMagento\Model\MappingShipping\Factory $mappingShippingFactory
     */
    public function __construct(
        Action\Context $context,
        \HiPay\FullserviceMagento\Model\MappingShipping\Factory $mappingShippingFactory
    ) {
        $this->mappingShippingFactory = $mappingShippingFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('mapping_shipping_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->mappingShippingFactory->create();
                $model->getResource()->load($model, $id);
                $title = $model->getName();
                $model->getResource()->delete($model);
                // display success message
                $this->messageManager->addSuccess(__('The mapping shipping has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_mappingshipping_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mappingshipping_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['mapping_shipping_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a mapping to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
