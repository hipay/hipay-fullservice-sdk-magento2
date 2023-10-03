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
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

define([
    'ko',
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/quote',
    'domReady!',
], function (ko, $, Component, fullScreenLoader, quote) {
    'use strict';
    return Component.extend({
        createHostedFields: function (context) {
            var self = this;
            if (context) {
                self = context;
            }
            self.hipayHostedFields = self.hipaySdk.create(
                'sdd',
                self.configHipay
            );

            self.hipayHostedFields.on('change', function (data) {
                if (!data.valid || data.error) {
                    self.hipayHFstatus = false;
                } else if (data.valid) {
                    self.hipayHFstatus = true;
                }
                self.isPlaceOrderAllowed(self.hipayHFstatus);
            });

            self.hipaySdk.injectBaseStylesheet();

            self.hipayHostedFields.on('blur', function (data) {
                // Get error container
                var domElement = document.querySelector(
                    "[data-hipay-id='hipay-sdd-field-error-" +
                        data.element +
                        "']"
                );

                // Finish function if no error DOM element
                if (!domElement) {
                    return;
                }

                // If not valid & not empty add error
                if (!data.validity.valid && !data.validity.empty) {
                    domElement.innerText = data.validity.error;
                } else {
                    domElement.innerText = '';
                }
            });

            self.hipayHostedFields.on('inputChange', function (data) {
                // Get error container
                var domElement = document.querySelector(
                    "[data-hipay-id='hipay-sdd-field-error-" +
                        data.element +
                        "']"
                );

                // Finish function if no error DOM element
                if (!domElement) {
                    return;
                }

                // If not valid & not potentiallyValid add error (input is focused)
                if (!data.validity.valid && !data.validity.potentiallyValid) {
                    domElement.innerText = data.validity.error;
                } else {
                    domElement.innerText = '';
                }
            });

            return self.hipayHostedFields;
        },

        initHostedFields: function () {
            var self = this;
            if (!this.hipaySdk.client) {
                var self = this;

                self.hipaySdk = new HiPay({
                    username: self.apiUsernameTokenJs,
                    password: self.apiPasswordTokenJs,
                    environment: self.env,
                    lang:
                        self.locale.length > 2
                            ? self.locale.substr(0, 2)
                            : 'en',
                });
            }

            self.createHostedFields();
            return true;
        },

        defaults: {
            template: 'HiPay_FullserviceMagento/payment/hipay-sdd',
            afterPlaceOrderUrl:
                window.checkoutConfig.payment.hiPayFullservice
                    .afterPlaceOrderUrl,
            env:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields.env
                    : '',
            apiUsernameTokenJs:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .apiUsernameTokenJs
                    : '',
            apiPasswordTokenJs:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .apiPasswordTokenJs
                    : '',
            color:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields.color
                    : '',
            fontFamily:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .fontFamily
                    : '',
            fontSize:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields.fontSize
                    : '',
            fontWeight:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .fontWeight
                    : '',
            placeholderColor:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .placeholderColor
                    : '',
            caretColor:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .caretColor
                    : '',
            iconColor:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields
                          .iconColor
                    : '',
            locale:
                window.checkoutConfig.payment.hiPayFullservice !== undefined
                    ? window.checkoutConfig.payment.hiPayFullservice.locale
                          .hipay_hosted_fields
                    : '',
            sdkJsUrl:
                window.checkoutConfig.payment.hipay_hosted_fields !== undefined
                    ? window.checkoutConfig.payment.hipay_hosted_fields.sdkJsUrl
                    : '',
            hipaySdk: '',
        },
        hipayHostedFields: null,
        configHipay: null,
        hipayHFstatus: false,
        isPlaceOrderAllowed: ko.observable(false),

        redirectAfterPlaceOrder: false,
        isLoading: ko.observable(false),
        placeOrderHandler: null,
        validateHandler: null,

        initObservable: function () {
            var self = this;
            self._super().observe([
                'paymentProduct',
                'browser_info',
                'gender',
                'firstname',
                'lastname',
                'iban',
                'bank_name',
            ]);

            return self;
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

        initialize: function () {
            var self = this;
            var customerFirstName = '';
            var customerLastName = '';

            self._super();

            var billingAddress = quote.billingAddress();
            if (billingAddress) {
                customerFirstName = billingAddress.firstname;
                customerLastName = billingAddress.lastname;
            }

            self.configHipay = {
                selector: 'hipay-container-sdd',
                fields: {
                    gender: {
                        selector: 'hipay-gender',
                    },
                    firstname: {
                        selector: 'hipay-firstname',
                        defaultValue: customerFirstName,
                    },
                    lastname: {
                        selector: 'hipay-lastname',
                        defaultValue: customerLastName,
                    },
                    iban: {
                        selector: 'hipay-iban',
                    },
                    bank_name: {
                        selector: 'hipay-bank-name',
                    },
                },
                styles: {
                    base: {
                        fontFamily: self.fontFamily,
                        color: self.color,
                        fontSize: self.fontSize,
                        fontWeight: self.fontWeight,
                        placeholderColor: self.placeholderColor,
                        caretColor: self.caretColor,
                        iconColor: self.iconColor,
                    },
                },
            };
        },

        /**
         *  Get global fingerprint  on dom load of checkout
         *
         * @returns {*}
         */
        getFingerprint: function () {
            if ($('#ioBB')) {
                return $('#ioBB').val();
            } else {
                return '';
            }
        },

        getData: function () {
            var self = this;
            var parent = self._super();
            var data = {
                method: self.item.method,
                additional_data: {
                    cc_type: self.paymentProduct(),
                    browser_info: self.browser_info(),
                    sdd_gender: self.gender(),
                    sdd_firstname: self.firstname(),
                    sdd_lastname: self.lastname(),
                    sdd_iban: self.iban(),
                    sdd_bank_name: self.bank_name(),
                },
            };
            return $.extend(true, parent, data);
        },

        getCode: function () {
            return 'hipay_sdd';
        },

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            $.mage.redirect(this.getAfterPlaceOrderUrl());
        },

        /**
         *  Get url for redirection
         *
         * @returns {*}
         */
        getAfterPlaceOrderUrl: function () {
            return this.afterPlaceOrderUrl[this.getCode()];
        },

        place_order: function (data, event) {
            var self = this;
            if (event) {
                event.preventDefault();
            }

            fullScreenLoader.startLoader();
            self.hipayHostedFields.getPaymentData().then(
                function (response) {
                    self.paymentProduct(response.payment_product);
                    self.browser_info(JSON.stringify(response.browser_info));
                    self.gender(response.gender);
                    self.firstname(response.firstname);
                    self.lastname(response.lastname);
                    self.iban(response.iban);
                    self.bank_name(response.bank_name);
                    self.placeOrder(
                        self.getData(),
                        self.redirectAfterPlaceOrder
                    );
                    fullScreenLoader.stopLoader();
                },
                function (errors) {
                    fullScreenLoader.stopLoader();
                }
            );
        },
    });
});
