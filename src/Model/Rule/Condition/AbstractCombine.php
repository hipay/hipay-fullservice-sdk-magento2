<?php

namespace HiPay\FullserviceMagento\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine;

abstract class AbstractCombine extends Combine {
	
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