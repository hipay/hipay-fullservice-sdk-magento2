define([
    'jquery',
    'mage/validation',
    'Magento_Payment/js/model/credit-card-validation/cvv-validator',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data'
], function($,validation,cvvValidator,creditCardData){
    'use strict';
    
    $.validator.addMethod(
        'validate-cvv-hipay', function (value, element) {
            if (creditCardData.creditCard.type != 'MI' || value != '' ) {
                var maxLength = creditCardData.creditCard ? creditCardData.creditCard.code.size : 3;
                return cvvValidator(value, maxLength).isValid;
            }
            return true;
        }, 
        $.mage.__('Please enter a valid credit card verification number.')
    );
});
