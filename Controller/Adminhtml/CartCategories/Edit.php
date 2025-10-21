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
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit payment profile
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
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
    private $cartCategoriesFactory;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * Edit constructor.
     *
     * @param Action\Context    $context
     * @param PageFactory       $resultPageFactory
     * @param Registry          $registry
     * @param Factory           $cartCategoriesFactory
     * @param Session           $backendSession
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Factory $cartCategoriesFactory,
        Session $backendSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->cartCategoriesFactory = $cartCategoriesFactory;
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
            ->addBreadcrumb(__('Mapping categories'), __('Create mapping'));

        return $resultPage;
    }

    /**
     * Edit Payment Profile page
     *
     * @return Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('mapping_id');
        $model = $this->cartCategoriesFactory->create();

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

        // 3. Set entered data if was error when we do save
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('cart_categories', $model);

        // 5. Build edit form
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Mapping Categories') : __('New Mapping Categories'),
            $id ? __('Edit Mapping Categories') : __('New Mapping Categories')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Mapping Categories'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Mapping Categories'));

        return $resultPage;
    }
}
