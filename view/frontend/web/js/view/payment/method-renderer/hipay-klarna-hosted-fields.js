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
    'Magento_Checkout/js/model/full-screen-loader'
], function (ko, $, Component, fullScreenLoader) {
    'use strict';
    return Component.extend({
        defaults: {
            configHipay: null,
            hipayHostedFields: null,
            redirectAfterPlaceOrder: false,
            afterPlaceOrderUrl:
            window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
                .hipay_klarna_hosted_fields,
            template: 'HiPay_FullserviceMagento/payment/hipay-hosted-fields-local',
            env: window.checkoutConfig.payment.hipay_klarna_hosted_fields
                ? window.checkoutConfig.payment.hipay_klarna_hosted_fields.env
                : 'stage',
            apiUsernameTokenJs: window.checkoutConfig.payment
                .hipay_klarna_hosted_fields
                ? window.checkoutConfig.payment.hipay_klarna_hosted_fields
                    .apiUsernameTokenJs
                : '',
            apiPasswordTokenJs: window.checkoutConfig.payment
                .hipay_klarna_hosted_fields
                ? window.checkoutConfig.payment.hipay_klarna_hosted_fields
                    .apiPasswordTokenJs
                : '',
            locale: window.checkoutConfig.payment.hiPayFullservice.locale
                ? window.checkoutConfig.payment.hiPayFullservice.locale
                    .hipay_klarna_hosted_fields
                : 'en_us'
        },
        isPlaceOrderAllowed: ko.observable(false),

        initialize: function () {
            var self = this;
            self._super();

            self.configHipay = {
                selector: `hipay-container-hosted-fields-${self.getProductCode()}`,
                template: 'auto'
            };
        },

        initHostedFields: function () {
            var self = this;

            self.hipaySdk = new HiPay({
                username: self.apiUsernameTokenJs,
                password: self.apiPasswordTokenJs,
                environment: self.env,
                lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
            });

            self.hipayHostedFields = self.hipaySdk.create(
                'klarna',
                self.configHipay
            );

            self.isPlaceOrderAllowed(true);

            return true;
        },

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
            var self = this;
            self._super().observe(['creditCardType', 'browser_info', 'payment_product']);

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
                    self.creditCardType(response.payment_product);
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
        getProductCode: function () {
            return 'klarna';
        },
        getCode: function () {
            return 'hipay_klarna_hosted_fields';
        },
        getData: function () {
            var self = this;
            var parent = self._super();
            var data = {
                method: self.item.method,
                additional_data: {
                    payment_product: self.payment_product(),
                    browser_info: self.browser_info()
                }
            };
            return $.extend(true, parent, data);
        },
        isActive: function () {
            return true;
        }
    });
});
