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
     	'ko',
        'HiPay_FullserviceMagento/js/view/payment',
        'HiPay_FullserviceMagento/js/model/iframe',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, ko, Component,iframe, fullScreenLoader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,
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
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            /**
             * Used in template to load iframe content
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },
            /**
             * After place order callback
             */
	        afterPlaceOrder: function () {
	        	 var self = this;

	        	if(this.isIframeMode() && !this.creditCardToken()){
	        		self.paymentReady(true);
	        	}
	        	else{
	        		
	        	 $.mage.redirect(this.getAfterPlaceOrderUrl());
	        	}
	        },
	        getData: function(){
            	return this._super(); 
            },
	        getAfterPlaceOrderUrl: function(){
	        	return this.afterPlaceOrderUrl[this.getCode()];
	        },
	        context: function() {
                return this;
            },
	        getCode: function() {
	            return 'hipay_hosted';
	        },
            isActive: function() {
                return true;
            },
            isIframeMode: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.isIframeMode[this.getCode()];
            },
            getIFrameUrl: function(){
            	return this.isInAction() ? this.getAfterPlaceOrderUrl() : '';
            },
            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                var self = this;
                if (this.placeOrder()) {
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            },
            /**
             * Hide loader when iframe is fully loaded.
             * @returns {void}
             */
            iframeLoaded: function() {
                fullScreenLoader.stopLoader();
            }
        });
    }
);

