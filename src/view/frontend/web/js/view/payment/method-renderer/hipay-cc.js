/*
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

define(
    [
     	'jquery',
     	'Magento_Payment/js/view/payment/cc-form',
     	'mage/storage',
     	'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($,Component,storage,fullScreenLoader) {
        'use strict';
        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc',
        		tokenizeUrl: window.checkoutConfig.payment.hipayCc.tokenizeUrl,
        		creditCardToken: null,
        		redirectAfterPlaceOrder: false
        	},
            placeOrderHandler: null,
            validateHandler: null,
            
            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },
        	/**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },
            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },
            /**
             * @returns {*}
             */
            getSource: function () {
                return window.checkoutConfig.payment.iframe.source[this.getCode()];
            },

            /**
             * @returns {*}
             */
            getControllerName: function () {
                return window.checkoutConfig.payment.iframe.controllerName[this.getCode()];
            },

            /**
             * @returns {*}
             */
            getPlaceOrderUrl: function () {
                return window.checkoutConfig.payment.iframe.placeOrderUrl[this.getCode()];
            },
            context: function() {
                return this;
            },
            /**
             * @override
             */
            getCode: function () {
                return 'hipay_cc';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'cc_token': this.creditCardToken
                    }
                };
            },
            /**
             * Display error message
             * @param {*} error - error message
             */
            addError: function (error) {
                if (_.isObject(error)) {
                    this.messageContainer.addErrorMessage(error);
                } else {
                    this.messageContainer.addErrorMessage({
                        message: error
                    });
                }
            },
            /**
             * After place order callback
             */
	        afterPlaceOrder: function () {
	        	 $.mage.redirect(window.checkoutConfig.payment.hipayCc.afterPlaceOrderUrl);
	        },
            generateToken: function (){
            	var self = this,
            	isPaymentProcessing = null;

	            if (this.validateHandler()) {
	            	
	            	 isPaymentProcessing = $.Deferred();
	                    $.when(isPaymentProcessing).done(
	                        function () {
	                            self.placeOrder(this.getData());
	                        }
	                    ).fail(
	                        function (error) {
	                            self.addError(error);
	                        }
	                    );

	                    storage.post(
	                    		fullScreenLoader.startLoader();
	                            this.tokenizeUrl, JSON.stringify(this.getData())
	                        ).done(
	                            function (response) {
	                            	console.log("response");
	                            	console.log(response);
	                            	this.creditCardToken = response.reponseText.token;
	                            	isPaymentProcessing.resolve();
	                            }
	                        ).fail(
	                            function (response) {
	                            	var error = JSON.parse(response.responseText);
	                                isPaymentProcessing.reject(error);
	                                fullScreenLoader.stopLoader();
	                            }
	                        );
	            	
	            }
            }
            
        });
    }
);

