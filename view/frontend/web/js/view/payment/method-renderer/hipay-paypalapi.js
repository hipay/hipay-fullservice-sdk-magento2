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
  'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted',
  'ko',
  'Magento_Checkout/js/view/payment/default',
  'Magento_Checkout/js/model/full-screen-loader',
  'Magento_Checkout/js/model/quote'
], function ($, ComponentHosted,ko, ComponentDefault, fullScreenLoader, quote) {
  'use strict';
  var merchantId = window.checkoutConfig.payment.hipay_paypalapi.merchant_id ?? '';
  if(merchantId.length > 0) {
    return ComponentDefault.extend({
      defaults: {
        template: 'HiPay_FullserviceMagento/payment/hipay-paypal',
        configHipay: null,
        hipayHostedFields: null,
        redirectAfterPlaceOrder: false,
        totals: quote.totals,
        placeOrderStatusUrl:
        window.checkoutConfig.payment.hiPayFullservice.placeOrderStatusUrl
            .hipay_paypalapi,
        afterPlaceOrderUrl:
        window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
            .hipay_paypalapi,
        env: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.env
            : 'stage',
        apiUsernameTokenJs: window.checkoutConfig.payment
            .hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi
                .apiUsernameTokenJs
            : '',
        apiPasswordTokenJs: window.checkoutConfig.payment
            .hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi
                .apiPasswordTokenJs
            : '',
        merchantId: merchantId,
        buttonLabel: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.button_label
            : 'pay',
        buttonShape: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.button_shape
            : 'pill',
        buttonColor: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.button_color
            : 'gold',
        buttonHeight: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.button_height
            : '40',
        bnpl: window.checkoutConfig.payment.hipay_paypalapi
            ? window.checkoutConfig.payment.hipay_paypalapi.bnpl
            : true,
        locale: window.checkoutConfig.payment.hiPayFullservice.locale
            ? window.checkoutConfig.payment.hiPayFullservice.locale
                .hipay_paypalapi
            : 'en_us'
      },
      isPlaceOrderAllowed: ko.observable(false),

      initialize: function () {
        var self = this;
        self._super();

        self.configHipay = {
          template: 'auto',
          selector: 'paypal-field',
          merchantPaypalId: self.merchantId,
          canPayLater: Boolean(self.bnpl),
          paypalButtonStyle: {
            shape: self.buttonShape,
            height: Number(self.buttonHeight),
            color: self.buttonColor,
            label: self.buttonLabel,
          },
          request: {
            amount: Number(quote.totals().base_grand_total.toFixed(2)),
            currency: quote.totals().quote_currency_code,
            locale: this.convertToUpperCaseAfterUnderscore(self.locale),
          }
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
            'paypal',
            self.configHipay
        );

        self.hipayHostedFields.on('paymentAuthorized', function (token) {
          self.paymentAuthorized(self, token);
        });

        self.isPlaceOrderAllowed(true);

        return true;
      },

      paymentAuthorized: function (self, tokenHipay) {
        self.browser_info(JSON.stringify(tokenHipay.browser_info));
        self.paypal_order_id(tokenHipay.orderID);
        self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
      },

      isPaypalV2Allowed: function () {
        var self = this;

        if (self.merchantId) {
          return true;
        }

        return false;
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
        self._super().observe(['paypal_order_id', 'browser_info']);

        return self;
      },

      afterPlaceOrder: function () {
        $.mage.redirect(this.afterPlaceOrderUrl);
      },

      context: function () {
        return this;
      },
      getProductCode: function () {
        return 'paypal';
      },
      getCode: function () {
        return 'hipay_paypalapi';
      },
      getData: function () {
        var self = this;
        var parent = self._super();
        var data = {
          method: self.item.method,
          additional_data: {
            paypal_order_id:self.paypal_order_id(),
            browser_info: self.browser_info(),
          }
        };
        return $.extend(true, parent, data);
      },
      isActive: function () {
        return true;
      },

      convertToUpperCaseAfterUnderscore: function(str){
        // Split the string at the underscore
        let parts = str.split('_');

        // Capitalize the second part
        parts[1] = parts[1].toUpperCase();

        // Join the parts back together
        return parts.join('_');
      }
    });

  } else {
    return ComponentHosted.extend({
      defaults: {
        template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
        redirectAfterPlaceOrder: false
      },

      getCode: function () {
        return 'hipay_paypalapi';
      },
      isActive: function () {
        return true;
      }
    });
  }

});
