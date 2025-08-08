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
    'domReady!'
], function ($, ko, Component, fullScreenLoader, quote) {
    'use strict';
    return Component.extend({
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

        initialize: function () {
            var self = this;
            self._super();

            self.configHipay = {
                selector: `hipay-container-oney-${self.getProductCode()}`,
                template: 'auto',
                request: {
                    amount: self.safeToFixed(Number(quote.totals().base_grand_total)),
                    currency: quote.totals().quote_currency_code
                }
            };

            // Update amount on cart total changes
            quote.totals.subscribe(function (totals) {
                if (self.hipayHostedFields) {
                    self.configHipay.request.amount = self.safeToFixed(Number(totals.base_grand_total));
                    self.configHipay.request.currency = totals.quote_currency_code;
                }
            });

            return self;
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

            self.hipayHostedFields.on('paymentAuthorized', function (response) {
                self.paymentAuthorized(self, response);
            });

            self.isPlaceOrderAllowed(true);
            return true;
        },

        paymentAuthorized: function (self, response) {
            self.payment_product(response.payment_product);
            self.browser_info(JSON.stringify(response.browser_info));
            self.oney_order_id(response.orderID);
            self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
        },

        setPlaceOrderHandler: function (handler) {
            this.placeOrderHandler = handler;
        },

        setValidateHandler: function (handler) {
            this.validateHandler = handler;
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
        },

        safeToFixed: function (value) {
            return Number(value).toFixed(2);
        }
    });
});
