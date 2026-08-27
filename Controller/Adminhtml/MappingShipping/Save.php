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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\MappingShipping;

use HiPay\FullserviceMagento\Model\MappingShipping\Factory;
use Magento\Backend\App\Action;
use HiPay\FullserviceMagento\Model\ResourceModel\MappingShipping\CollectionFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save Mapping Shipping
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    protected $_mappingShippingCollectionFactory;

    /**
     * @var Factory
     */
    private $mappingShippingFactory;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * Save constructor.
     *
     * @param Action\Context    $context
     * @param CollectionFactory $mappingShippingCollectionFactory
     * @param Factory           $mappingShippingFactory
     * @param Session           $backendSession
     */
    public function __construct(
        Action\Context $context,
        CollectionFactory $mappingShippingCollectionFactory,
        Factory $mappingShippingFactory,
        Session $backendSession
    ) {
        $this->_mappingShippingCollectionFactory = $mappingShippingCollectionFactory;
        $this->mappingShippingFactory = $mappingShippingFactory;
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
            $model = $this->mappingShippingFactory->create();
            $id = $this->getRequest()->getParam('mapping_shipping_id');

            if ($id) {
                $model->load($id);
            } else {
                // Prevent duplicate mappings
                if ($data['magento_shipping_code'] !== 'hipay_shipping_custom') {
                    $count = $this->_mappingShippingCollectionFactory->create()
                        ->addFieldToFilter('magento_shipping_code', $data['magento_shipping_code'])
                        ->count();

                    if ($count > 0) {
                        $this->messageManager->addErrorMessage(__('You have already done this mapping.'));
                        $this->_getSession()->setFormData($data);
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]
                        );
                    }
                } else {
                    $count = $this->_mappingShippingCollectionFactory->create()
                        ->addFieldToFilter('magento_shipping_code_custom', $data['magento_shipping_code_custom'])
                        ->count();

                    if ($count > 0) {
                        $this->messageManager->addErrorMessage(__('You have already done this mapping.'));
                        $this->_getSession()->setFormData($data);
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]
                        );
                    }
                }
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'hipay_mappingshipping_prepare_save',
                ['mappingshipping' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved this mapping shipping.'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['mapping_shipping_id' => $model->getId(), '_current' => true]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException | \RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the mapping.')
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['profile_id' => $this->getRequest()->getParam('mapping_shipping_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
