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
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
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
                template: 'HiPay_FullserviceMagento/payment/hipay-astropay',
                nationalIdentification: '',
                redirectAfterPlaceOrder: false,
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
                paymentForm: $("#co-astropay-form")
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

            /**
             *
             * @returns {*}
             */
            validate: function(){
                var form = '#co-transparent-form';
                return $(form).validation() && $(form).validation('isValid');
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'nationalIdentification',
                    ]);
                
                this.paymentForm.validation();
                return this;
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'nationalIdentification': this.nationalIdentification(),
                    }
                };
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

            /**
             *  Get global fingerprint  on dom load of checkout
             *
             * @returns {*}
             */
            getFingerprint: function () {
                if ($('#ioBB')) {
                    return $('#ioBB').val();
                }else{
                    return '';
                }
            },

            /**
             * Type identification
             *
             * @returns {*}
             */
            getTypeIdentification: function(){
                return window.checkoutConfig.payment.hiPayFullservice.typeIdentification[this.getCode()];
            },

            /**
             * After place order callback
             */
	        afterPlaceOrder: function () {
	        	 $.mage.redirect(this.afterPlaceOrderUrl[this.getCode()]);
	        },
        });
    }
);

