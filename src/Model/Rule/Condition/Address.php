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
 * Rule Address Class
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
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
    
    /**
     * 
     * @var \Magento\Config\Model\Config\Source\Locale\Currency
     */
    protected $_currencies;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    
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
    	\Magento\Config\Model\Config\Source\Locale\Currency $currencies,
    	array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_localeDate = $context->getLocaleDate();
        $this->_directoryCountry = $directoryCountry;
        $this->_directoryAllregion = $directoryAllregion;
        $this->_shippingAllmethods = $shippingAllmethods;
        $this->_paymentAllmethods = $paymentAllmethods;
        $this->_currencies = $currencies;
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
        	'base_grand_total' => __('Grand Total'),
        	'base_currency_code' => __('Currency'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
       		'created_at' => __("Order's time"),
            'payment_method' => __('Payment Method'),
       		'billing_postcode' => __('Billing Postcode'),
       		'billing_region' => __('Billing Region'),
        	'billing_region_id' => __('Billing State/Province'),
        	'billing_country_id' => __('Billing Country'),
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
            case 'base_grand_total':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
            case 'billing_country_id': 
            case 'billing_region_id': 
            case 'base_currency_code':
                return 'select';
            case 'created_at':
            	return 'boolean' ;
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
            case 'billing_country_id': 
            case 'billing_region_id': 
            case 'base_currency_code': 
            case 'created_at':
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
                case 'billing_country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                case 'billing_region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;
                case 'base_currency_code':
                	$options = $this->_currencies->toOptionArray(false);
                case 'created_at':
                	$options = [
                		["value"=>"00::8","label"=>__("Midnight - 8:00 a.m.")],
                		["value"=>"8::15","label"=>__("8:00 a.m. - 3:00 p.m.")],
                		["value"=>"15::20","label"=>__("3:00 pm. - 8:00 p.m.")],
                		["value"=>"20::23","label"=>__("8:00 p.m. - 11:59 p.m.")],
                	];
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
            if ($model->isVirtual()) {
                $address = $model->getBillingAddress();
            } else {
                $address = $model->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getPayment()->getMethod());
        }
        
        //add custom validation
        $address->setBillingPostcode($model->getBillingAddress()->getPostcode());
        $address->setBillingRegion($model->getBillingAddress()->getRegion());
        $address->setBillingRegionId($model->getBillingAddress()->getRegionId());
        $address->setBillingCountryId($model->getBillingAddress()->getCountryId());
        
        $address->setBaseCurrencyCode($model->getBaseCurrencyCode());
        
        $address->setCreatedAt($this->_getFormatCreatedAt($model));
        
        if(!$model->isVirtual()){//Get infos from shipping address
        	$address->setShippingPostcode($model->getShippingAddress()->getPostcode());
        	$address->setShippingRegion($model->getShippingAddress()->getRegion());
        	$address->setShippingRegionId($model->getShippingAddress()->getRegionId());
        	$address->setShippingCountryId($model->getShippingAddress()->getCountryId());
        	$address->setWeight($address->getWeight());
			$address->setShippingMethod($address->getShippingMethod());
        }

        return parent::validate($address);
    }
    
    protected function _getFormatCreatedAt($object)
    {
    	$created_at = $object->getCreatedAt();
    
    	if(!$created_at instanceof \DateTime)
    	{    		
    		$created_at = $this->_localeDate->scopeDate($object->getStoreId(), $created_at, true);
    	}
    
    		$hour = (int)$created_at->format("HH");
    
    		switch (true) {
    			case ($hour >= 0 && $hour <= 8):
    				return '00::8';
    			case ($hour > 8 && $hour <= 15):
    				return '8::15';
    			case ($hour > 15 && $hour <= 20):
    				return '15::20';
    			case ($hour > 20 && $hour <= 23):
    				return '20::23';
    
    		}
    
    		return '';
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
