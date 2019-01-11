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
        'ko',
        'jquery',
        'HiPay_FullserviceMagento/js/view/payment/cc-form',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'domReady!'
    ],
    function (ko, $, Component, storage, fullScreenLoader,quote) {
        'use strict';

        return Component.extend({

            createHostedFields: function(context) {
                var self = this;
                if (context) {
                     self = context;
                }
                self.hipayHostedFields = self.hipaySdk.create("card", self.configHipay);
                self.hipayHostedFields.on("change", function (data) {
                    if (!data.valid && data.error) {
                        self.addError(data.error);
                        self.isPlaceOrderActionAllowed(false);
                    } else if (data.valid) {
                        self.isPlaceOrderActionAllowed(true);
                    }
                });

                return self.hipayHostedFields;
            },

            initHostedFields: function () {
                var self = this;
                if (!this.hipaySdk.client) {
                    self.initHiPayConfiguration(self.createHostedFields);
                } else {
                    self.createHostedFields();
                }
                return true;
            },

            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-hosted-fields',
                showCcForm: true,
                env: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.env : "",
                apiUsernameTokenJs: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.apiUsernameTokenJs : "",
                apiPasswordTokenJs: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.apiPasswordTokenJs : "",
                color: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.color : "",
                fontFamily: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.fontFamily : "",
                fontSize: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.fontSize : "",
                fontWeight: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.fontWeight : "",
                placeholderColor: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.placeholderColor : "",
                caretColor: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.caretColor : "",
                iconColor: (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.iconColor : "",
                locale: (window.checkoutConfig.payment.hiPayFullservice !== undefined) ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_hosted_fields : "",
                sdkJsUrl:  (window.checkoutConfig.payment.hipay_hosted_fields !== undefined) ? window.checkoutConfig.payment.hipay_hosted_fields.sdkJsUrl : "",
                hipaySdk: ""
            },

            hipayHostedFields: null,
            configHipay: null,

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            initialize: function () {
                var self = this;
                var customerFirstName = "";
                var customerLastName = "";

                this._super();

                this.isPlaceOrderActionAllowed(false);

                var billingAddress = quote.billingAddress();
                if (billingAddress) {
                    customerFirstName = billingAddress.firstname;
                    customerLastName = billingAddress.lastname;
                }

                this.configHipay = {
                    selector: "hipay-container-hosted-fields",
                    multi_use: this.allowOneclick.hipay_hosted_fields,
                    fields: {
                        cardHolder: {
                            selector: "hipay-field-cardHolder",
                            defaultFirstname: customerFirstName,
                            defaultLastname: customerLastName
                        },
                        cardNumber: {
                            selector: "hipay-field-cardNumber"
                        },
                        expiryDate: {
                            selector: "hipay-field-expiryDate"
                        },
                        cvc: {
                            selector: "hipay-field-cvc",
                            helpButton: true,
                            helpSelector: "hipay-help-cvc"
                        }
                    },
                    styles: {
                        base: {
                            fontFamily: this.fontFamily,
                            color: this.color,
                            fontSize: this.fontSize,
                            fontWeight: this.fontWeight,
                            placeholderColor: this.placeholderColor,
                            caretColor: this.caretColor,
                            iconColor: this.iconColor
                        }
                    }
                };
            },
            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },


            /**
             * @override
             */
            initObservable: function () {
                var self = this;
                this._super();

                this.showCcForm = ko.computed(function () {
                    var showCC = !(self.useOneclick() && self.customerHasCard()) ||
                        self.selectedCard() === undefined ||
                        self.selectedCard() === '';
                    return showCC;
                }, this);

                return this;
            },

            context: function () {
                return this;
            },

            /**
             * @override
             */
            getCode: function () {
                return 'hipay_hosted_fields';
            },

            getData: function () {
                return this._super();
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                $.mage.redirect(this.getAfterPlaceOrderUrl());
            },

            generateToken: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.creditCardToken()) {
                    self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
                    return;
                }

                fullScreenLoader.startLoader();
                this.hipayHostedFields.createToken()
                    .then(function (response) {
                            self.creditCardToken(response.token);

                            self.creditCardType(response.payment_product);
                            self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
                            self.creditCardToken("");
                            fullScreenLoader.stopLoader();
                        },
                        function (error) {
                            self.addError(error);
                            fullScreenLoader.stopLoader();
                        }
                    );
            }
        });
    }
);

