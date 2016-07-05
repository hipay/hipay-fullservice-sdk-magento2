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
        'Magento_Checkout/js/view/payment/default',
    ],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-qiwiwallet',
                qiwiUserId: '',
                redirectAfterPlaceOrder: false,
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
                paymentForm: $("co-qiwiwallet-form")
            },
            /**
             * Handler used by transparent
             */
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
            initialize: function() {
                var self = this;
                this._super();

            },
            initObservable: function () {
                this._super()
                    .observe([
                        'qiwiUserId',
                    ]);
                
                this.paymentForm.validation();
                return this;
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'qiwiuser': this.qiwiUserId(),
                    }
                };
            },
	        getCode: function() {
	            return 'hipay_qiwiwallet';
	        },
	        /**
	         * Needed by transparent.js
	         */
	        context: function() {
                return this;
            },
            isActive: function() {
                return true;
            },
            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },
            validate: function(){
            	return (this.paymentForm.validation && this.paymentForm.validation('isValid'));

            },
            /**
             * After place order callback
             */
	        afterPlaceOrder: function () {
	        	 $.mage.redirect(this.afterPlaceOrderUrl);
	        },
        });
    }
);

