define(['domReady'], function (domReady) {
    'use strict';

    function toggleFields(e) {
        var merchantId = e.target.value;
        ['button_color', 'button_shape', 'button_label', 'button_height', 'bnpl'].forEach(function (fieldId) {
            var field = document.getElementById('payment_us_hipay_paypalapi_' + fieldId);
            field.disabled = (merchantId === '');
        });
    }

    domReady(function() {
        var merchantIdInput = document.getElementById('payment_us_hipay_paypalapi_merchant_id');
        if (merchantIdInput) {
            toggleFields({ target: merchantIdInput }); // Initial call with the current value
            merchantIdInput.addEventListener('input', toggleFields);
        }
    });
});
