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
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */

define([
        "jquery",
        "Magento_Rule/rules"
], function(jQuery,VarienRulesForm){

    VarienRulesForm.prototype.addRuleNewChild = function (elem) {
            var parent_id = elem.id.replace(/^.*__(.*)__.*$/, '$1');
            var children_ul = $(elem.id.replace(/__/g, ':').replace(/[^:]*$/, 'children').replace(/:/g, '__'));
            var max_id = 0, i;
            var children_inputs = Selector.findChildElements(children_ul, $A(['input.hidden']));
            if (children_inputs.length) {
                children_inputs.each(function(el){
                    if (el.id.match(/__type$/)) {
                        i = 1 * el.id.replace(/^.*__.*?([0-9]+)_.*__.*$/, '$1');// modified form clean payment method name
                        max_id = i > max_id ? i : max_id;
                    }
                });
            }
            var new_id = parent_id + '--' + (max_id + 1);
            var new_type = elem.value;
            var new_elem = document.createElement('LI');
            new_elem.className = 'rule-param-wait';
            new_elem.innerHTML = jQuery.mage.__('This won\'t take long . . .');
            children_ul.insertBefore(new_elem, $(elem).up('li'));

            new Ajax.Request(this.newChildUrl, {
                evalScripts: true,
                parameters: {form_key: FORM_KEY, type:new_type.replace('/','-'), id:new_id },
                onComplete: this.onAddNewChildComplete.bind(this, new_elem),
                onSuccess: function(transport) {
                    if(this._processSuccess(transport)) {
                        $(new_elem).update(transport.responseText);
                    }
                }.bind(this),
                onFailure: this._processFailure.bind(this)
            });
        };
    
    return VarienRulesForm;
});