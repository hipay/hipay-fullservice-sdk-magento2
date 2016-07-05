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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
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
            getIframeWidth: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.iFrameWidth[this.getCode()];
            },
            
            getIframeHeight: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.iFrameHeight[this.getCode()];
            },
            getIframeStyle: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.iFrameStyle[this.getCode()];
            },
            getIframeWrapperStyle: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.iFrameWrapperStyle[this.getCode()];
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

