<?php
/**
 * HiPay fullservice Magento2
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

namespace HiPay\FullserviceMagento\Block\Adminhtml\Cartcategories\Edit;

use \HiPay\Fullservice\Data\Category\Collection as collection;

/**
 * Adminhtml Cart Categories edit form block
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\CategoriesMagento
     */
    protected $categoriesMagento;

    /**
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\CategoriesHipay
     */
    protected $categoriesHipay;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \HiPay\FullserviceMagento\Model\System\Config\Source\CategoriesMagento $categoriesMagento,
        \HiPay\FullserviceMagento\Model\System\Config\Source\CategoriesHipay $categoriesHipay,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->categoriesMagento = $categoriesMagento;
        $this->categoriesHipay = $categoriesHipay;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setHtmlIdPrefix('cart_categories_');
        $model = $this->_coreRegistry->registry('cart_categories');

        $fieldset = $form->addFieldset(
            'cart_categories_fieldset',
            ['legend' => __('Mapping Categories'), 'class' => 'fieldset-wide']
        );

        $options = $this->categoriesMagento->toOptionArray();
        $fieldset->addField(
            'category_magento_id',
            'select',
            [
                'name' => 'category_magento_id',
                'label' => __('Category Magento'),
                'title' => __('Category Magento'),
                'values' => $options,
                'required' => true
            ]
        );

        $options = $this->categoriesHipay->toOptionArray();
        $fieldset->addField(
            'category_hipay_id',
            'select',
            [
                'name' => 'category_hipay_id',
                'label' => __('Category Hipay'),
                'title' => __('Category Hipay'),
                'values' => $options,
                'required' => true
            ]
        );

        $this->_eventManager->dispatch('adminhtml_cart_categories_edit_prepare_form', ['form' => $form]);

        if ($model->getMappingId() !== null) {
            $form->addField('mapping_id', 'hidden', ['name' => 'mapping_id', 'value' => $model->getMappingId()]);
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
