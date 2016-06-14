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
     	'ko',
     	'jquery',
     	'HiPay_FullserviceMagento/js/view/payment/cc-form',
     	'hipay_tpp',
     	'mage/storage',
     	'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (ko, $,Component,TPP,storage,fullScreenLoader) {

        'use strict';
        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc',
        		showCcForm: true,
        		apiUsernameTokenJs: window.checkoutConfig.payment.hipayCc.apiUsernameTokenJs ,
        		apiPasswordTokenJs: window.checkoutConfig.payment.hipayCc.apiPasswordTokenJs
        		
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
             * @override
             */
            initObservable: function () {
            	var self = this;
                this._super();
                
                this.showCcForm = ko.computed(function () {

                    return !(self.useOneclick() && self.customerHasCard()) ||
                    		self.selectedCard() === undefined ||
                    		self.selectedCard() === '';
                }, this);

                return this;
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
            context: function() {
                return this;
            },
            hasSsCardType: function() {
                return false;
            },
            getCcAvailableTypes: function() {
                return window.checkoutConfig.payment.hipayCc.availableTypes;
            },
            /**
             * @override
             */
            getCode: function () {
                return 'hipay_cc';
            },
            getData: function() {
                return this._super();
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
	        	 $.mage.redirect(this.getAfterPlaceOrderUrl());
	        },
            generateToken: function (data,event){
            	var self = this,
            	isTokenizeProcessing = null;
            	

                if (event) {
                    event.preventDefault();
                }
                
	            if(this.validateHandler()){

	            	 if(this.creditCardToken()){
	            		 	self.placeOrder(self.getData(),self.redirectAfterPlaceOrder);
	            		 	return;
	                 }
	            	
	            	 isTokenizeProcessing = $.Deferred();
	                    $.when(isTokenizeProcessing).done(
	                        function () {
	                            self.placeOrder(self.getData(),self.redirectAfterPlaceOrder);
	                        }
	                    ).fail(
	                        function (error) {
	                            self.addError(error);
	                        }
	                    );
	                    
	                    fullScreenLoader.startLoader();
	                    
	                    TPP.setTarget(window.checkoutConfig.payment.hipayCc.env);
	                    TPP.setCredentials(apiUsernameTokenJs,apiPasswordTokenJs);
	                    
	                    TPP.create({
	                        card_number:  this.creditCardNumber(),
	                        cvc: this.creditCardVerificationNumber(),
	                        card_expiry_month:this.creditCardExpMonth(),
	                        card_expiry_year: this.creditCardExpYear(),
	                        card_holder: '',
	                        multi_use: '0'
	                      },
		                      function (response) {
		                          	if(response.token){
		                          		self.creditCardToken(response.token);
		                          		isTokenizeProcessing.resolve();
		                          	}
		                          	else{
		                          		var error = response;
			                                isTokenizeProcessing.reject(error);
		                          	}
		                          	fullScreenLoader.stopLoader();
	                          },
	                          function (response) {
	                            	var error = response;
	                            	isTokenizeProcessing.reject(error);
	                                fullScreenLoader.stopLoader();
	                            }
	                      );
	                    
	            }

            }
            
        });
    }
);

