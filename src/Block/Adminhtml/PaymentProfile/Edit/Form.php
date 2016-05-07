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
	 * 
	 * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory  $ppFactory
	 */
    protected $ppFactory;
	
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
			\HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory,
			array $data = []
			) {

				parent::__construct($context,$registry,$formFactory, $data);
				$this->ppFactory = $ppFactory;
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
    	
    	$options = $this->ppFactory->create()->getAllPaymentTypes();
    	$fieldset->addField(
    			'period_unit',
    			'select',
    			[
    					'name' => 'period_unit',
    					'label' => __('Periode Unit'),
    					'title' => __('Periode Unit'),
    					'values'=>$options
    			]
    			);
    	
    	$fieldset->addField(
    			'period_frequency',
    			'text',
    			[
    					'name' => 'period_frequency',
    					'label' => __('Periode Frequency'),
    					'title' => __('Periode Frequency')
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
    	$form->setUseContainer(true);
    	$this->setForm($form);
    
    	return parent::_prepareForm();
    }

}
