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
    'jquery',
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/quote',
    'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-payment-mixin',
    'domReady!'
], function ($, ko, Component, fullScreenLoader, quote, hipayPaymentMixin) {
    'use strict';
    return hipayPaymentMixin(
        Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-oney',
                configHipay: null,
                hipayHostedFields: null,
                redirectAfterPlaceOrder: false,
                afterPlaceOrderUrl: '',
                env: 'stage',
                apiUsernameTokenJs: '',
                apiPasswordTokenJs: '',
                paymentProductFees: '',
                locale: 'fr_FR',
                isPlaceOrderAllowed: ko.observable(false),
                lastAmount: null,
                lastCurrency: null
            },

            initialize: function () {
                var self = this;
                self._super();

                self.configHipay = {
                    selector: `hipay-container-oney-${self.getProductCode()}`,
                    template: 'auto',
                    request: {
                        amount: Number(quote.totals().base_grand_total),
                        currency: quote.totals().quote_currency_code
                    }
                };
                self.lastAmount = self.configHipay.request.amount;
                self.lastCurrency = self.configHipay.request.currency;

                let refreshTimeout = null;
                quote.totals.subscribe(function (totals) {
                    if (!totals) return;

                    var newAmount = Number(totals.base_grand_total);
                    var newCurrency = totals.quote_currency_code;

                    if (newAmount === self.lastAmount && newCurrency === self.lastCurrency) {
                        return;
                    }

                    self.lastAmount = newAmount;
                    self.lastCurrency = newCurrency;

                    if (refreshTimeout) {
                        clearTimeout(refreshTimeout);
                    }
                    refreshTimeout = setTimeout(function () {
                        self.refreshHostedFields(newAmount, newCurrency);
                    }, 500);
                });

                self.initMiniCartListener();

                return self;
            },

            refreshHostedFields: function (amount, currency) {
                var self = this;
                if (!self.hipaySdk) {
                    return;
                }

                if (self.hipayHostedFields) {
                    try {
                        self.hipayHostedFields.destroy();
                    } catch (e) {
                        console.warn('Unable to destroy the HiPay Oney instance', e);
                    }
                    self.hipayHostedFields = null;
                }

                self.configHipay.request.amount = amount;
                self.configHipay.request.currency = currency;

                self.hipayHostedFields = self.hipaySdk.create(
                    self.paymentProductFees,
                    self.configHipay
                );
            },

            isRadioButtonVisible: function () {
                return true;
            },

            isPlaceOrderActionAllowed: function () {
                return this.isPlaceOrderAllowed();
            },

            initHostedFields: function () {
                var self = this;
                self.hipaySdk = new HiPay({
                    username: self.apiUsernameTokenJs,
                    password: self.apiPasswordTokenJs,
                    environment: self.env,
                    lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'fr'
                });

                self.hipayHostedFields = self.hipaySdk.create(self.paymentProductFees, self.configHipay);

                self.isPlaceOrderAllowed(true);
                return true;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            initObservable: function () {
                var self = this;
                self._super().observe(['payment_product', 'browser_info']);
                return self;
            },

            place_order: function (data, event) {
                var self = this;
                if (event) {
                    event.preventDefault();
                }

                fullScreenLoader.startLoader();
                self.hipayHostedFields.getPaymentData().then(
                    function (response) {
                        self.payment_product(response.payment_product);
                        self.browser_info(JSON.stringify(response.browser_info));
                        self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
                        fullScreenLoader.stopLoader();
                    },
                    function (errors) {
                        fullScreenLoader.stopLoader();
                    }
                );
            },

            afterPlaceOrder: function () {
                $.mage.redirect(this.afterPlaceOrderUrl);
            },

            context: function () {
                return this;
            },

            getData: function () {
                var self = this;
                var parent = self._super();
                var data = {
                    method: self.item.method,
                    additional_data: {
                        payment_product: self.payment_product(),
                        browser_info: self.browser_info(),
                    }
                };
                return $.extend(true, parent, data);
            },

            isActive: function () {
                return true;
            }
        })
    );
});
