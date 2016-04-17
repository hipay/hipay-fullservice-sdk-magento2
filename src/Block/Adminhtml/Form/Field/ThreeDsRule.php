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
namespace HiPay\FullserviceMagento\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class ThreeDsRule extends Field
{
	
	/**
	 * Check if columns are defined, set template
	 *
	 */
	public function __construct()
	{
		/*if (!$this->_addButtonLabel) {
			$this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
		}*/
		parent::__construct();
		if (!$this->getTemplate()) {
			$this->setTemplate('HiPay_FullserviceMagento::system/config/form/field/rules.phtml');
		}
	}
	
	public function getNewChildUrl(){
		return Mage::helper("adminhtml")->getUrl('*/rule/newConditionHtml',array('form'=>'rule_conditions_fieldset'));
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{

		$partsId = explode("_", $element->getId());
		$method_code = $partsId[1]. "_" . $partsId[2];
		$rule = Mage::getModel('hipay/rule');
		$rule->setMethodCode($method_code);
		
		if($element->getValue())
			$rule->load($element->getValue());
		
		if($rule->getConfigPath() == "")
			$rule->setConfigPath($element->getId());

		$element->setRule($rule);
		
		$this->setElement($element);
		return $this->_toHtml();
	}
}