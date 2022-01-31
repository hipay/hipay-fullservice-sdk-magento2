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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Block sortable checkboxes
 * used on Credit card API configuration
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CheckboxesSortable extends Field
{
    /**
     * Add js to sort checkboxes
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $javaScript = '
            <script type="text/javascript">
			 	require(["jquery","jquery/ui"], function($){
				    
		 		var options = $("#row_' . $element->getHtmlId() . ' td.value div.nested div");
		 		options.each(function(){
		 				var input = $(this).find("input").first();
		 				var nameArray = input.attr("name") + \'[]\';
		 				input.attr("name",nameArray);
		 				
		 				var label = $(this).find("label").first();
		 				label.css("cursor","move");
		 				label.attr("for",false);
	
				});
		 		
					$( "#row_' . $element->getHtmlId() . ' td.value div.nested" ).sortable();
					  	 
				});
            </script>';
        $element->setData('after_element_html', $javaScript . $element->getAfterElementHtml());

        return parent::_getElementHtml($element);
    }
}
