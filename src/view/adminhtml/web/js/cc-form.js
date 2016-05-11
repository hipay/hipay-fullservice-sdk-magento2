/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "hiPayTpp",
    "jquery/ui"
], function($, hiPayTpp){
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

        prepare : function(event, method) {
            if (method.indexOf('hipay') > -1) {
                this.preparePayment();
            }
        },
        preparePayment: function() {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
        },
        submitAdminOrder: function() {
            var ccNumber = $("#" + this.code + "_cc_number").val(),
                ccExprYr = $("#" + this.code + "_expiration_yr").val(),
                ccExprMo = $("#" + this.code + "_expiration").val(),
                cvc = $('#" + this.code + "_cc_cid').val(),
                self = this;
            

            if (ccNumber) {
                
                
                TPP.setTarget(this.env);
                TPP.setCredentials(this.apiUsername, this.apiPassword);
                
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
                          		$('#" + this.code + "_cc_token').val(response.token);
                          		 order._realSubmit();
                          	}
                          	else{
                          		var error = response;
	                            console.log(error);
                          	}

                      },
                      function (response) {
                        	var error = response;
                        	console.log(error);
                        }
                  );
                

                
            } 
        },

        _create: function() {
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
        }
    });

    return $.hipay.fullserviceCcForm;
});