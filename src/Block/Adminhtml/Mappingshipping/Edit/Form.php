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

namespace HiPay\FullserviceMagento\Block\Adminhtml\Mappingshipping\Edit;

use \HiPay\Fullservice\Data\Category\Collection as collection;

/**
 * Adminhtml Cart Categories edit form block
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\ShippingMethodsMagento
     */
    protected $shippingMethodsMagento;

    /**
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\ShippingMethodsHipay
     */
    protected $shippingMethodsHipay;


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
        \HiPay\FullserviceMagento\Model\System\Config\Source\ShippingMethodsMagento $shippingMethodsMagento,
        \HiPay\FullserviceMagento\Model\System\Config\Source\ShippingMethodsHipay $shippingMethodsHipay,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_shippingMethodsMagento = $shippingMethodsMagento;
        $this->_shippingMethodsHipay = $shippingMethodsHipay;
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
        $form->setHtmlIdPrefix('cart_mappingshipping_');
        $model = $this->_coreRegistry->registry('cart_mapping_shipping');

        $fieldset = $form->addFieldset(
            'cart_mappingshipping_fieldset',
            ['legend' => __('Mapping Shipping Method'), 'class' => 'fieldset-wide']
        );

        $options = $this->_shippingMethodsMagento->toOptionArray();
        $config = [
            'name' => 'magento_shipping_code',
            'label' => __('Magento Shipping methods'),
            'title' => __('Magento Shipping methods'),
            'required' => true,
            'values' => $options
        ];

        if ($model != null) {
            if ($model->getMappingShippingId() !== null) {
                $config = array_merge($config,['disabled' => true]);
            }
        }
        
        $fieldset->addField(
            'magento_shipping_code',
            'select',
            $config
        );


        $options = $this->_shippingMethodsHipay->toOptionArray();
        $fieldset->addField(
            'hipay_shipping_id',
            'select',
            [
                'name' => 'hipay_shipping_id',
                'label' => __('HiPay Shipping methods'),
                'title' => __('HiPay Shipping methods'),
                'values' => $options
            ]
        );

        $fieldset->addField(
            'delay_preparation',
            'text',
            [
                'name' => 'delay_preparation',
                'label' => __('Order preparation delay'),
                'title' => __('Order preparation delay'),
                'note' => __('Estimated time in days to prepare your orders.')
            ]
        );
        $fieldset->addField(
            'delay_delivery',
            'text',
            [
                'name' => 'delay_delivery',
                'label' => __('Delivery time estimated'),
                'title' => __('Delivery time estimated'),
                'note' => __('Estimated time in days of delivery. (Without preparation time)')
            ]
        );

        $this->_eventManager->dispatch('adminhtml_cart_mappingshipping_edit_prepare_form', ['form' => $form]);

        if ($model != null) {
            if ($model->getMappingShippingId() !== null){
                $form->addField('mapping_shipping_id', 'hidden', ['name' => 'mapping_shipping_id', 'value' => $model->getMappingShippingId()]);
            }
            $form->setValues($model->getData());
        }


        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
