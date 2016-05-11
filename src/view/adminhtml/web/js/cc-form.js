/**
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

/*jshint jquery:true*/
define([
    "jquery",
    "hiPayTpp",
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function($, TPP,alert){
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
            if (method.indexOf('hipay') > -1) {
                this.preparePayment();
            }
        },
        preparePayment: function() {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
        },
        submitAdminOrder: function() {
            var ccNumber = $("#" + this.options.code + "_cc_number").val(),
                ccExprYr = $("#" + this.options.code + "_expiration_yr").val(),
                ccExprMo = $("#" + this.options.code + "_expiration").val(),
                cvc = $("#" + this.options.code + "_cc_cid").val(),
                self = this;

            if (ccNumber) {
                
                
                TPP.setTarget(this.options.env);
                TPP.setCredentials(this.options.apiUsername, this.options.apiPassword);
                console.log({
                    card_number:  ccNumber,
                    cvc: cvc,
                    card_expiry_month:ccExprMo,
                    card_expiry_year: ccExprYr,
                    card_holder: '',
                    multi_use: '0'
                  });
                TPP.create({
                    card_number:  ccNumber,
                    cvc: cvc,
                    card_expiry_month:ccExprMo,
                    card_expiry_year: ccExprYr,
                    card_holder: '',
                    multi_use: '0'
                  },
                      function (response) {
                          	if(response.token){
                          		
                          		$("#" + self.options.code + "_card_token").val(response.token);
                          		order._realSubmit();
                          	}
                          	else{
                          		var error = response;
                          		self._processErrors(error);
                          		$('#edit_form').trigger('processStop');
                          	}

                      },
                      function (response) {
                        	var error = response;
                        	self._processErrors(error.message);
                        	$('#edit_form').trigger('processStop');
                        }
                  );
                

                
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