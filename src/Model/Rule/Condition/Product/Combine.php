<?php
/*
 * HiPay fullservice SDK
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
namespace HiPay\FullserviceMagento\Model\Rule\Condition\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product
     */
    protected $_ruleConditionProd;
    
    protected $methodCode = null;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \HiPay\FullserviceMagento\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ruleConditionProd = $ruleConditionProduct;
        $this->setType('HiPay\FullserviceMagento\Model\Rule\Condition\Product\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'HiPay\FullserviceMagento\Model\Rule\Condition\Product\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Cart Item Attribute'), 'value' => $iAttributes],
                ['label' => __('Product Attribute'), 'value' => $pAttributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
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
