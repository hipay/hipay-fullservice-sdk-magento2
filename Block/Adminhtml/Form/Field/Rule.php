<?php

/**
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Block\Adminhtml\Form\Field;

use HiPay\FullserviceMagento\Model\Rule\Factory;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Block sortable checkboxes
 * used for 3ds and oneclick on payment methods configuration
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Rule extends Field
{
    /**
     * @var Factory $ruleFactory
     */
    private $ruleFactory;

    /**
     * Check if columns are defined, set template
     *
     * Rule constructor.
     *
     * @param Context $context
     * @param Factory $ruleFactory
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Factory $ruleFactory,
        array $data = []
    ) {

        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $data);

        if (!$this->getTemplate()) {
            $this->setTemplate('HiPay_FullserviceMagento::system/config/form/field/rules.phtml');
        }
    }

    /**
     * Get URL to load a new condition HTML block dynamically
     *
     * @return string
     */
    public function getNewChildUrl()
    {
        return $this->getUrl('hipay_rule/rule/newConditionHtml/form/rule_conditions_fieldset');
    }

    /**
     * Retrieve element HTML markup
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $rule = $this->ruleFactory->create();

        $field = $element->getFieldConfig()['id'];
        list(, $methodCode) = explode('/', $element->getFieldConfig()['path'] ?: '');
        $configPath = 'payment/' . $methodCode . '/' . $field;

        $rule->setMethodCode($methodCode);

        if ($element->getValue()) {
            $rule->load($element->getValue());
        }

        if ($rule->getConfigPath() == "") {
            $rule->setConfigPath($configPath);
        }

        $element->setRule($rule);
        $this->setElement($element);

        return $this->_toHtml();
    }
}
