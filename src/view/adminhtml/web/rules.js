define([
        "jquery",
        "Magento_Rule/rules"
], function(jQuery,VarienRulesForm){

    VarienRulesForm.addRuleNewChild = function (elem) {
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