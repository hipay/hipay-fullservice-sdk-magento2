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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

define([
  'ko',
  'jquery',
  'Magento_Checkout/js/view/payment/default',
  'Magento_Checkout/js/action/place-order',
  'Magento_Checkout/js/model/quote',
  'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-payment-mixin',
  'mage/storage',
  'HiPay_FullserviceMagento/js/model/toc-agreements-tracker'
], function (
  ko,
  $,
  Component,
  placeOrderAction,
  quote,
  hipayPaymentMixin,
  storage,
  tocTracker
) {
  'use strict';

  var googlePayConfig = window.checkoutConfig.payment.hipay_googlepay || {};
  var hipayConfig = window.checkoutConfig.payment.hiPayFullservice;

  return hipayPaymentMixin(
    Component.extend({
      defaults: {
        template: 'HiPay_FullserviceMagento/payment/hipay-googlepay',
        creditCardToken: null,
        creditCardType: null,
        browserInfo: null,
        fingerprint: null,
        totals: quote.totals,
        eci: hipayConfig.defaultEci,
        placeOrderStatusUrl: hipayConfig.placeOrderStatusUrl.hipay_googlepay,
        afterPlaceOrderUrl: hipayConfig.afterPlaceOrderUrl.hipay_googlepay,
        env: googlePayConfig.env || 'stage',
        apiUsernameTokenJs: googlePayConfig.apiUsernameTokenJs || '',
        apiPasswordTokenJs: googlePayConfig.apiPasswordTokenJs || '',
        displayName: googlePayConfig.display_name || '',
        locale: hipayConfig.locale ? hipayConfig.locale.hipay_googlepay : 'en_us'
      },

      hipaySdk: null,
      instanceGooglePay: null,
      currentRequest: null,
      isGooglePayAllowed: ko.observable(true),
      isAllTOCChecked: ko.observable(!tocTracker.hasMandatoryAgreements()),
      isGooglePayVisibleToPay: ko.observable(false),

      initialize: function () {
        this._super();
        this.initMiniCartListener();
      },

      onGooglePayButtonRendered: function () {
        var self = this;

        if (self._googlePayInitStarted) {
          return;
        }
        self._googlePayInitStarted = true;

        self.initTOCEvents();
        self.createGooglePay();

        // no update() on the SDK wallet: recreate it when the request changes
        self.totals.subscribe(function () {
          self.refreshGooglePay();
        });
        quote.billingAddress.subscribe(function () {
          self.refreshGooglePay();
        });
      },

      initHipaySdk: function () {
        if (!this.hipaySdk) {
          this.hipaySdk = new HiPay({
            username: this.apiUsernameTokenJs,
            password: this.apiPasswordTokenJs,
            environment: this.env,
            lang: this.locale.length > 2 ? this.locale.substr(0, 2) : 'en'
          });
        }

        return this.hipaySdk;
      },

      initTOCEvents: function () {
        tocTracker.register(
          "input[id*='agreement_hipay_googlepay']",
          '.checkout-agreements input[type="checkbox"][id*="agreement"]',
          this.isAllTOCChecked
        );
      },

      /**
       * Google Pay request, amount and currency from the mixin
       */
      getGooglePayRequest: function () {
        var transaction = this.getTransactionAmount(quote.totals());

        return {
          amount: transaction.amount,
          currencyCode: transaction.currencyCode,
          countryCode: this.getCountryCodeWithFallback(),
          supportedNetworks: ['visa', 'mastercard', 'maestro', 'cb']
        };
      },

      createGooglePay: function () {
        var self = this;

        self.currentRequest = self.getGooglePayRequest();
        try {
          self.instanceGooglePay = self.initHipaySdk().create('wallet', {
            selector: 'hipay-googlepay',
            provider: 'googlepay',
            displayName: self.displayName,
            existingPaymentMethodRequired: false,
            request: self.currentRequest
          });
        } catch (e) {
          console.warn('Google Pay initialization failed:', e);
          self.isGooglePayAllowed(false);
          return;
        }

        self.instanceGooglePay.on('ready', function () {
          self.isGooglePayAllowed(true);
        });

        self.instanceGooglePay.on('unavailable', function () {
          self.isGooglePayAllowed(false);
        });

        self.instanceGooglePay.on('paymentAuthorized', function (response) {
          self.paymentAuthorized(response);
        });

        self.instanceGooglePay.on('paymentFailed', function () {
          self.handlePaymentFailure();
        });
      },

      refreshGooglePay: function () {
        var request = this.getGooglePayRequest();

        if (!this.instanceGooglePay || !this.isGooglePayAllowed()
          || JSON.stringify(request) === JSON.stringify(this.currentRequest)) {
          return;
        }

        this.instanceGooglePay.destroy();
        this.instanceGooglePay = null;
        this.createGooglePay();
      },

      handlePaymentFailure: function () {
        this.messageContainer.addErrorMessage({
          message: $.mage.__('Google Pay payment could not be completed.')
        });
      },

      paymentAuthorized: function (response) {
        var self = this;

        self.creditCardToken(response.token);
        self.creditCardType(response.payment_product.toLowerCase().replace(/ /g, '-'));
        self.browserInfo(JSON.stringify(response.browser_info));
        self.fingerprint(response.device_fingerprint);

        $.when(placeOrderAction(self.getData(), self.messageContainer))
          .done(function () {
            storage
              .get(self.placeOrderStatusUrl)
              .done(function (statusResponse) {
                if (statusResponse && statusResponse.redirectUrl) {
                  $.mage.redirect(statusResponse.redirectUrl);
                } else {
                  $.mage.redirect(self.getPendingUrl());
                }
              })
              .fail(function () {
                $.mage.redirect(self.afterPlaceOrderUrl);
              });
          });
      },

      initObservable: function () {
        var self = this;
        self._super().observe(['creditCardToken', 'creditCardType', 'browserInfo', 'fingerprint', 'eci']);

        quote.billingAddress.subscribe(function () {
          self.handleOrderValidation();
        });
        quote.shippingAddress.subscribe(function () {
          self.handleOrderValidation();
        });
        quote.paymentMethod.subscribe(function () {
          self.handleOrderValidation();
        });
        quote.shippingMethod.subscribe(function () {
          self.handleOrderValidation();
        });

        self.handleOrderValidation();

        return self;
      },

      handleOrderValidation: function () {
        var validation = this.validateOrderPlacement();
        this.isGooglePayVisibleToPay(validation.canPlace);

        return validation;
      },

      getCode: function () {
        return 'hipay_googlepay';
      },

      getData: function () {
        return {
          method: this.item.method,
          additional_data: {
            card_token: this.creditCardToken(),
            eci: this.eci(),
            cc_type: this.creditCardType(),
            browser_info: this.browserInfo(),
            fingerprint: this.fingerprint()
          }
        };
      },

      getPendingUrl: function () {
        return window.BASE_URL + 'hipay/redirect/pending?payment_method=' + this.getCode();
      }
    })
  );
});
