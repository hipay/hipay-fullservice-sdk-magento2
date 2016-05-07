<?php
/*
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Block\Adminhtml\PaymentProfile\Edit;

/**
 * Adminhtml payment profile edit form block
 *
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
    
    	/** @var \Magento\Framework\Data\Form $form */
    	$form = $this->_formFactory->create();
    
    	$form->setHtmlIdPrefix('paymentprofile_');
    
    	$model = $this->_coreRegistry->registry('payment_profile');
    
    	$fieldset = $form->addFieldset(
    			'paymentprofile_fieldset',
    			['legend' => __('Payment Profile'), 'class' => 'fieldset-wide']
    			);
    
    	$fieldset->addField(
    			'name',
    			'text',
    			[
    					'name' => 'name',
    					'label' => __('Name'),
    					'title' => __('Name')
    			]
    			);
    
    	$fieldset->addField(
    			'period_unit',
    			'text',
    			[
    					'name' => 'period_unit',
    					'label' => __('Periode Unit'),
    					'title' => __('Periode Unit')
    			]
    			);
    	
    	$fieldset->addField(
    			'period_max_cycles',
    			'text',
    			[
    					'name' => 'period_max_cycles',
    					'label' => __('Periode Max Cycles'),
    					'title' => __('Periode Max Cycles')
    			]
    			);
    
    	$this->_eventManager->dispatch('adminhtml_hipay_paymentprofile_edit_prepare_form', ['form' => $form]);
    
    	$form->setValues($model->getData());
    
    	$this->setForm($form);
    
    	return parent::_prepareForm();
    }

}
