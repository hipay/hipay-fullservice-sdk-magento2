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
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $_objectManager;
	
	
	/**
	 * Check if columns are defined, set template
	 *
	 */
	public function __construct(
			\Magento\Backend\Block\Template\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			array $data = [])
	{
		
		$this->_objectManager = $objectManager;
		
		parent::__construct($context, $data);
		
		if (!$this->getTemplate()) {
			$this->setTemplate('HiPay_FullserviceMagento::system/config/form/field/rules.phtml');
		}
	}
	
	public function getNewChildUrl(){
		return $this->getUrl('hipay_rule/rule/newConditionHtml/form/rule_conditions_fieldset');
	}
	
	/**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
	protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		$rule = $this->_objectManager->create('HiPay\FullserviceMagento\Model\Rule');
        $partsId = explode("_", $element->getId());
        $method_code = $partsId[1]. "_" . $partsId[2];
        $rule->setMethodCode($method_code);                                                                                                                                                                                                           
                                                                                                                                                                                                                                                              
        if($element->getValue()){                                                                                                                                                                                                                     
        	$rule->load($element->getValue());                                                                                                                                                                                                    
        }                                                                                                                                                                                                                                             
                                                                                                                                                                                                                                                              
        if($rule->getConfigPath() == ""){                                                                                                                                                                                                             
            $rule->setConfigPath($element->getId());                                                                                                                                                                                              
        }                                                                                                                                                                                                                                             
                                                                                                                                                                                                                                                              
        $element->setRule($rule);                                                                                                                                                                                                                     
		$this->setElement($element);                                                                                                                                                                                                                                  
        
		return $this->_toHtml();   
	}
}