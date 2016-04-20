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
namespace HiPay\FullserviceMagento\Model\Rule\Condition;

class Address extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_directoryCountry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Allregion
     */
    protected $_directoryAllregion;

    /**
     * @var \Magento\Shipping\Model\Config\Source\Allmethods
     */
    protected $_shippingAllmethods;

    /**
     * @var \Magento\Payment\Model\Config\Source\Allmethods
     */
    protected $_paymentAllmethods;
    
    protected $methodCode = null;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_directoryCountry = $directoryCountry;
        $this->_directoryAllregion = $directoryAllregion;
        $this->_shippingAllmethods = $shippingAllmethods;
        $this->_paymentAllmethods = $paymentAllmethods;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'base_subtotal' => __('Subtotal'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'payment_method' => __('Payment Method'),
            'shipping_method' => __('Shipping Method'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        return parent::validate($address);
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
    	$elt = $this->getForm()->addField(
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
    	$elt->setShowAsText(true);
    	return $elt;
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
    
    
}
