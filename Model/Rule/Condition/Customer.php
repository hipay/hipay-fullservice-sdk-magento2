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

use Magento\Framework\Convert\DataObject;

/**
 * Rule Customer Class
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     *
     * @var \Magento\Config\Model\Config\Source\Yesno $_yesNo
     */
    protected $_yesNo;

    /**
     *
     * @var \Magento\Customer\Model\Config\Source\Group\Multiselect $_customerGroups
     */
    protected $_customerGroups;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory $_orderFactory
     */
    protected $_orderFactory;

    protected $methodCode = null;

    /**
     * Customer constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Customer\Model\Config\Source\Group\Multiselect $customerGroups
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\Config\Source\Group\Multiselect $customerGroups,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_customerGroups = $customerGroups;
        $this->_yesNo = $yesNo;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'orders_count' => __('Orders count'),
            'customer_is_guest' => __('Customer is guest'),
            'diff_addresses' => __('Billing and shipping addresses are differents'),
            'customer_group' => __('Customer Groups')

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
            case 'orders_count':
                return 'numeric';
            case 'customer_is_guest':
            case 'diff_addresses':
                return 'boolean';
            case 'customer_group':
                return 'multiselect';
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
            case 'customer_is_guest':
            case 'diff_addresses':
                return 'select';
            case 'customer_group':
                return 'multiselect';
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
                case 'customer_is_guest':
                case 'diff_addresses':
                    $options = $this->_yesNo->toOptionArray();
                    break;
                case 'customer_group':
                    $options = $this->_customerGroups->toOptionArray();
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
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $model;
        $toValidate = new DataObject();

        $customer_id = $quote->getCustomerId();
        $orders_count = $this->_orderFactory->create()->getCollection()
            ->addAttributeToFilter('customer_id', $customer_id)
            ->count();
        $toValidate->setOrdersCount($orders_count);
        $toValidate->setCustomerIsGuest($quote->getCustomerIsGuest() === nulll ? 0 : $quote->getCustomerIsGuest());
        $toValidate->setDiffAddresses($this->_addressesesAreDifferent($quote));
        $toValidate->setCustomerGroup($quote->getCustomerGroupId());

        return parent::validate($toValidate);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean $isDifferent
     */
    protected function _addressesesAreDifferent($quote)
    {
        $isDifferent = 0;
        if ($quote->getIsVirtual()) {
            return $isDifferent;
        }

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();
        $methods = [ 'getStreetFull', 'getCity', 'getCountryId', 'getPostcode', 'getRegionId' ];

        foreach ($methods as $method_name) {
            $billingValue = call_user_func([ $billingAddress, $method_name ]);
            $shippingValue = call_user_func([ $shippingAddress, $method_name ]);
            if ($billingValue != $shippingValue) {
                $isDifferent = 1;
                break;
            }
        }

        return $isDifferent;
    }

    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
        $this->setData('method_code', $this->methodCode);
        return $this;
    }

    public function setConfigPath($configPath)
    {
        $this->elementName = 'rule_' . $configPath;
        $this->setData('config_path', $configPath);
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
