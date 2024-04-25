define(['domReady'], function (domReady) {
    'use strict';

    // Function to disable or enable fields based on the value of merchant_id
    function toggleFields() {
        var merchantId = document.getElementById('payment_us_hipay_paypalapiv2_merchant_id').value;
        var inputFields = [
            'button_layout',
            'button_colour',
            'button_shape',
            'button_label',
            'button_height',
            'show_pay_later'
        ];

        inputFields.forEach(function (fieldId) {
            var fullFieldId = 'payment_us_hipay_paypalapiv2_' + fieldId;
            var field = document.getElementById(fullFieldId);

            if (merchantId === '') {
                field.disabled = true;
            } else {
                field.disabled = false;
            }
        });
    }
    domReady(function() {
        var merchantIdSelector = 'payment_us_hipay_paypalapiv2_merchant_id';
        if (document.getElementById(merchantIdSelector) !== null) {
            toggleFields();

            document.getElementById(merchantIdSelector).addEventListener('change', function () {
                toggleFields();
            });
        }
    });


});
