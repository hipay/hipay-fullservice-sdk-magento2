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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Block\Adminhtml\System\Config;

use HiPay\FullserviceMagento\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Read-only field displaying the installed HiPay module version.
 *
 * @author    Hipay
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ModuleVersion extends Field
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Data    $helper
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Render the installed module version instead of an input field.
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return '<span class="hipay-module-version">'
            . $this->escapeHtml($this->helper->getExtensionVersion())
            . '</span>';
    }
}
