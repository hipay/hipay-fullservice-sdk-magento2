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
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl.hipay_facilypay4X,
                env: window.checkoutConfig.payment.hipay_facilypay4X
                    ? window.checkoutConfig.payment.hipay_facilypay4X.env
                    : 'stage',
                apiUsernameTokenJs: window.checkoutConfig.payment.hipay_facilypay4X
                    ? window.checkoutConfig.payment.hipay_facilypay4X.apiUsernameTokenJs
                    : '',
                apiPasswordTokenJs: window.checkoutConfig.payment.hipay_facilypay4X
                    ? window.checkoutConfig.payment.hipay_facilypay4X.apiPasswordTokenJs
                    : '',
                paymentProductFees: window.checkoutConfig.payment.hipay_facilypay4X
                    ? window.checkoutConfig.payment.hipay_facilypay4X.paymentProductFees
                    : '',
                locale: window.checkoutConfig.payment.hiPayFullservice.locale
                    ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_facilypay4X
                    : 'fr_FR'
            },
            isPlaceOrderAllowed: ko.observable(false),
            lastAmount: null,
            lastCurrency: null,

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
                        console.warn('Impossible de détruire l’instance HiPay Oney', e);
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

            initObservable: function () {
                var self = this;
                self._super().observe(['payment_product', 'browser_info', 'oney_order_id']);
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
                        self.oney_order_id(response.orderID);
                        self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
                        fullScreenLoader.stopLoader();
                    },
                    function () {
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

            getProductCode: function () {
                return '4xcb';
            },

            getCode: function () {
                return 'hipay_facilypay4X';
            },

            getData: function () {
                var self = this;
                var parent = self._super();
                var data = {
                    method: self.item.method,
                    additional_data: {
                        payment_product: self.payment_product(),
                        browser_info: self.browser_info(),
                        oney_order_id: self.oney_order_id()
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
