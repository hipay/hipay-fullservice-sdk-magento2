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

namespace HiPay\FullserviceMagento\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * ColorPicker for adminhtml field
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HipayColorPicker extends Field
{
    /**
     * Render HTML for the color picker input field in admin configuration
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('class', 'color-picker');
        $html = $element->getElementHtml();
        $html .= '<script type="text/javascript">
            require(["jquery", "spectrum"], function($) {
                $(function() {
                    $(".color-picker").spectrum({
                        showInput: true,
                        allowEmpty: true,
                        showInitial: true,
                        preferredFormat: "hex",
                        clickoutFiresChange: true,
                        showButtons: true
                    });
                });
            });
        </script>';

        return $html;
    }
}
