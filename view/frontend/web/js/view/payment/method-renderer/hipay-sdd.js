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
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate'
    ],
    function ($,ko,Component,$t) {
        'use strict';
        return Component.extend({

            defaults: {
                sdd_bank_name:'',
                sdd_gender: '',
                sdd_code_bic:'',
                sdd_iban:'',
                sdd_firstname:'',
                sdd_lastname:'',
                template: 'HiPay_FullserviceMagento/payment/hipay-sdd',
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
            },
            redirectAfterPlaceOrder: false,
            isLoading: ko.observable(false),
            placeOrderHandler: null,
            validateHandler: null,

            initObservable: function () {
                this._super()
                    .observe([
                        'sdd_gender',
                        'sdd_bank_name',
                        'sdd_code_bic',
                        'sdd_iban',
                        'sdd_firstname',
                        'sdd_lastname',
                    ]);
                return this;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             *
             * @returns {jQuery}
             */
            validate: function() {
                var form = '#co-transparent-form';
                return $(form).validation() && $(form).validation('isValid');
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
             *  Get data of view in place method
             *
             * @returns {{method, additional_data: {sdd_bank_name: *, sdd_code_bic: *, sdd_iban: *, sdd_firstname: *, sdd_lastname: *, sdd_gender: *, cc_type: string}}}
             */
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'sdd_bank_name': this.sdd_bank_name(),
                        'sdd_code_bic': this.sdd_code_bic(),
                        'sdd_iban': this.sdd_iban(),
                        'sdd_firstname': this.sdd_firstname(),
                        'sdd_lastname': this.sdd_lastname(),
                        'sdd_gender': this.sdd_gender(),
                        'cc_type':'SDD',
                    }
                };
            },

            initialize: function(){
                this._super();
            },

            getCode: function() {
                return 'hipay_sdd';
            },

            isActive: function() {
                return true;
            },

            /**
             * After place order callback ( Redirect or not according the configuration)
             */
            afterPlaceOrder: function () {
                var self = this;

                this.redirectAfterPlaceOrder = true;
            },

            /**
             *  Get url for redirection
             *
             * @returns {*}
             */
            getAfterPlaceOrderUrl: function(){
                return this.afterPlaceOrderUrl[this.getCode()];
            },

        });
    }
);

