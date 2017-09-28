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
namespace HiPay\FullserviceMagento\Model\Rule\Condition;

/**
 * Rule Combine Class
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
/**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $_conditionAddress;
    
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Customer
     */
    protected $_conditionCustomer;
    
    protected $methodCode = null;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \HiPay\FullserviceMagento\Model\Rule\Condition\Address $conditionAddress,
    	\HiPay\FullserviceMagento\Model\Rule\Condition\Customer $conditionCustomer,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_conditionAddress = $conditionAddress;
        $this->_conditionCustomer = $conditionCustomer;
        parent::__construct($context, $data);
        $this->setType('HiPay\FullserviceMagento\Model\Rule\Condition\Combine');
        
    }
    
   

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }
        
        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $cAttributes = [];
        foreach ($customerAttributes as $code=>$label) {
        	$cAttributes[] = [
        			'value'=>'HiPay\FullserviceMagento\Model\Rule\Condition\Customer|'.$code,
        			'label'=>$label
        			
        	];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Product\Found',
                    'label' => __('Product attribute combination'),
                ],
                [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Product\Subselect',
                    'label' => __('Products subselection')
                ],
                [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Combine',
                    'label' => __('Conditions combination')
                ],
                ['label' => __('CartCategories Attribute'), 'value' => $attributes],
            	['label' => __('Customer Attribute'), 'value' => $cAttributes]
            ]
        );

        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('hipayrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
    
    public function setMethodCode($methodCode){
    	$this->methodCode = $methodCode;
    	$this->setData('method_code',$this->methodCode);
    	return $this;
    }
    
    public function setConfigPath($configPath){
    	$this->elementName = 'rule_' . $configPath;
    	$this->setData('config_path',$configPath);
    	return $this;
    }
    
    /**
     * @return AbstractElement
     */
    public function getTypeElement()
    {
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__type',
    			'hidden',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
    					'value' => $this->getType(),
    					'no_span' => true,
    					'class' => 'hidden'
    			]
    			);
    }
    
    /**
     * @return $this
     */
    public function getAttributeElement()
    {
    	if (null === $this->getAttribute()) {
    		foreach (array_keys($this->getAttributeOption()) as $option) {
    			$this->setAttribute($option);
    			break;
    		}
    	}
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__attribute',
    			'select',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
    					'values' => $this->getAttributeSelectOptions(),
    					'value' => $this->getAttribute(),
    					'value_name' => $this->getAttributeName()
    			]
    			)->setRenderer(
    					$this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
    					);
    }
    
    
    /**
     * Retrieve Condition Operator element Instance
     * If the operator value is empty - define first available operator value as default
     *
     * @return \Magento\Framework\Data\Form\Element\Select
     */
    public function getOperatorElement()
    {
    	$options = $this->getOperatorSelectOptions();
    	if ($this->getOperator() === null) {
    		foreach ($options as $option) {
    			$this->setOperator($option['value']);
    			break;
    		}
    	}
    
    	$elementId = sprintf('%s__%s__operator', $this->getPrefix(), $this->getId() . '_' . $this->getConfigPath());
    	$elementName = sprintf($this->elementName . '[%s][%s][operator]', $this->getPrefix(), $this->getId());
    	$element = $this->getForm()->addField(
    			$elementId,
    			'select',
    			[
    					'name' => $elementName,
    					'values' => $options,
    					'value' => $this->getOperator(),
    					'value_name' => $this->getOperatorName()
    			]
    			);
    	$element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
    
    	return $element;
    }
    
    /**
     * @return $this
     */
    public function getValueElement()
    {
    	$elementParams = [
    			'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
    			'value' => $this->getValue(),
    			'values' => $this->getValueSelectOptions(),
    			'value_name' => $this->getValueName(),
    			'after_element_html' => $this->getValueAfterElementHtml(),
    			'explicit_apply' => $this->getExplicitApply(),
    	];
    	if ($this->getInputType() == 'date') {
    		// date format intentionally hard-coded
    		$elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
    		$elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
    	}
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__value',
    			$this->getValueElementType(),
    			$elementParams
    			)->setRenderer(
    					$this->getValueElementRenderer()
    					);
    }
    
    /**
     * @return $this
     */
    public function getNewChildElement()
    {
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__new_child',
    			'select',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
    					'values' => $this->getNewChildSelectOptions(),
    					'value_name' => $this->getNewChildName()
    			]
    			)->setRenderer(
    					$this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
    					);
    }
    
    /**
     * @return string
     */
    public function asHtmlRecursive()
    {
    	$html = $this->asHtml() .
    	'<ul id="' .
    	$this->getPrefix() .
    	'__' .
    	$this->getId() .
    	'_' .
    	$this->getConfigPath() .
    	'__children" class="rule-param-children">';
    	foreach ($this->getConditions() as $cond) {
    		$cond->setMethodCode($this->methodCode);
    		$cond->setConfigPath($this->getConfigPath());
    		$html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
    	}
    	$html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
    	return $html;
    }
    
    /**
     * @return object
     */
    public function getAggregatorElement()
    {
    	if ($this->getAggregator() === null) {
    		foreach (array_keys($this->getAggregatorOption()) as $key) {
    			$this->setAggregator($key);
    			break;
    		}
    	}
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() .'_' . $this->getConfigPath() . '__aggregator',
    			'select',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][aggregator]',
    					'values' => $this->getAggregatorSelectOptions(),
    					'value' => $this->getAggregator(),
    					'value_name' => $this->getAggregatorName()
    			]
    			)->setRenderer(
    					$this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
    					);
    }
    
    
    
   
}
