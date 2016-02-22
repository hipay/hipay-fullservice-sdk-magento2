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
            generateToken: function (){
            	var self = this,
            	isPaymentProcessing = null;

	            if (this.validateHandler()) {
	            	
	            	 isPaymentProcessing = $.Deferred();
	                    $.when(isPaymentProcessing).done(
	                        function () {
	                            self.placeOrder();
	                        }
	                    ).fail(
	                        function (error) {
	                            self.addError(error);
	                        }
	                    );
	                    console.log(this.tokenizeUrl);
	                    console.log(JSON.stringify(this.getData()));
	                    storage.post(
	                            this.tokenizeUrl, JSON.stringify(this.getData())
	                        ).done(
	                            function (response) {
	                            	console.log("response");
	                            	console.log(response);
	                            	isPaymentProcessing.resolve();
	                            }
	                        ).fail(
	                            function (response) {
	                            	var error = JSON.parse(response.responseText);
	                                console.log('error');
	                                console.log(error);
	                                isPaymentProcessing.reject(error);
	                                fullScreenLoader.stopLoader();
	                            }
	                        );
	            	
	            }
            }
            
        });
    }
);

