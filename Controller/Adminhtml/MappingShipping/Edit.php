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
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit Mappin Shipping
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Edit extends Action
{

    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Factory
     */
    private $mappingShippingFactory;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Registry    $registry
     * @param Factory     $mappingShippingFactory
     * @param Session     $backendSession
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Factory $mappingShippingFactory,
        Session $backendSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->mappingShippingFactory = $mappingShippingFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('HiPay_FullserviceMagento::hipay_cart_categories')
            ->addBreadcrumb(__('HiPay'), __('HiPay'))
            ->addBreadcrumb(__('Mapping shipping'), __('Create mapping shipping'));

        return $resultPage;
    }

    /**
     * Edit Mapping Shipping page
     *
     * @return  Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('mapping_shipping_id');
        $model = $this->mappingShippingFactory->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This mapping no longer exists.'));

                /**
                 * @var Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if there was an error when we did save
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('cart_mapping_shipping', $model);

        // 5. Build edit form
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Mapping Shipping') : __('New Mapping Shipping Methods'),
            $id ? __('Edit Mapping Shipping') : __('New Mapping Shipping Methods')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Mapping Shipping'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Mapping Shipping'));

        return $resultPage;
    }
}
