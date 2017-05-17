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
 * Save Mapping Shipping
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\MappingShipping\CollectionFactory
     */
    protected $_mappingShippingCollectionFactory;

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
                                \HiPay\FullserviceMagento\Model\ResourceModel\MappingShipping\CollectionFactory $mappingShippingCollectionFactory)
    {
        parent::__construct($context);
        $this->_mappingShippingCollectionFactory = $mappingShippingCollectionFactory;
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
            $model = $this->_objectManager->create('HiPay\FullserviceMagento\Model\MappingShipping');
            $id = $this->getRequest()->getParam('mapping_shipping_id');
            if ($id) {
                $model->load($id);
            } else {
                $count = $this->_mappingShippingCollectionFactory->create()
                    ->addFieldToFilter('magento_shipping_code', $data['magento_shipping_code'])
                    ->addFieldToFilter('hipay_shipping_id', $data['hipay_shipping_id'])
                    ->count();

                if ($count > 1) {
                    $this->messageManager->addErrorMessage(__('You have already done this mapping.'));
                    $this->_getSession()->setFormData($data);
                    return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]);
                }
            }

            $model->setData($data);
            $this->_eventManager->dispatch(
                'hipay_mappingshipping_prepare_save',
                ['mappingshipping' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this mapping shipping.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['mapping_shipping_id' => $model->getId(), '_current' => true]);
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
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
