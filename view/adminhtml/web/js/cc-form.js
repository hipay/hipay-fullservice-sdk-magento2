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
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */

/*jshint jquery:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function($,alert){
    "use strict";

    $.widget('hipay.fullserviceCcForm', {
        options: {
            submitSelectors: ['.save', '#submit_order_top_button'],
            code : "hipay_cc",
            env: "STAGE",
            apiUsername: '',
            apiPassword: '',
        },
        ccNumber: "",
        ccExprYr: "",
        ccExprMo: "",
        cvc: "",
        hipay: "",
        hipaySDK: "",
        
        _create: function() {
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
        },
        
        prepare : function(event, method) {
            if (method && method.indexOf('hipay_cc') > -1) {
                this.preparePayment();
            }

            require([ this.options.sdkJsUrl], function (hipay) {
                self.hipay = hipay;
            });

        },
        preparePayment: function() {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
        },
        submitAdminOrder: function() {
            var self = this;

            self.hipaySDK = HiPay({
                username: this.options.apiUsername,
                password: this.options.apiPassword,
                environment: this.options.env
            });

            var ccNumber = $("#" + this.options.code + "_cc_number").val();
            if (ccNumber) {
                var params = {
                    cardNumber: ccNumber,
                    cvc: $("#" + this.options.code + "_cc_cid").val(),
                    expiryMonth: $("#" + this.options.code + "_expiration").val().padStart(2, '0'),
                    expiryYear: $("#" + this.options.code + "_expiration_yr").val().substr(-2),
                    cardHolder: $("#" + this.options.code + "_cc_owner").val(),
                    multiUse: 0
                };

                self.hipaySDK.tokenize(params)
                    .then(function (response) {
                            if(response.token){
                                $("#" + self.options.code + "_card_token").val(response.token);
                                order._realSubmit();
                            }
                            else{
                                self._processErrors(response);
                                $('#edit_form').trigger('processStop');
                            }
                        },
                        function (error) {
                            self._processErrors(error);
                            $('#edit_form').trigger('processStop');
                        }
                    );
            } 
            else{
                self._processErrors("Cc Number is empty!");
            	$('#edit_form').trigger('processStop');
            }
        },
        /**
         * Processing errors
         *
         * @param response
         * @private
         */
        _processErrors: function (msg) {
            if (typeof (msg) === 'object') {
                alert({
                    content: msg.join("\n")
                });
            }
            if (msg) {
                alert({
                    content: msg
                });
            }
        }

    });

    return $.hipay.fullserviceCcForm;
});