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
  "ko",
  "jquery",
  "Magento_Checkout/js/view/payment/default",
  "Magento_Checkout/js/action/place-order",
  "Magento_Checkout/js/model/quote",
  "mage/storage",
], function (ko, $, Component, placeOrderAction, quote, storage) {
  "use strict";

  var canMakeApplePay = ko.observable(false);
  return Component.extend({
    defaults: {
      template: "HiPay_FullserviceMagento/payment/hipay-applepay",
      creditCardToken: null,
      creditCardType: "cb",
      instanceApplePay: null,
      totals: quote.totals,
      eci: window.checkoutConfig.payment.hiPayFullservice.defaultEci,
      placeOrderStatusUrl:
        window.checkoutConfig.payment.hiPayFullservice.placeOrderStatusUrl
          .hipay_applepay,
      afterPlaceOrderUrl:
        window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
          .hipay_applepay,
      env: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.env
        : "stage",
      apiUsernameTokenJs: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.apiUsernameTokenJs
        : "",
      apiPasswordTokenJs: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.apiPasswordTokenJs
        : "",
      merchantId: window.checkoutConfig.payment.hipay_applepay.merchant_id,
      displayName: window.checkoutConfig.payment.hipay_applepay.display_name,
      buttonType: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.button_type
        : "plain",
      buttonColor: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.button_color
        : "black",
      locale: window.checkoutConfig.payment.hiPayFullservice.locale
        ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_applepay
        : "en_us",
    },

    placeOrderHandler: null,
    validateHandler: null,
    isAllTOCChecked: ko.observable(!window.checkoutConfig.checkoutAgreements.isEnabled),
    allTOC: new Map(),

    initHostedFields: function (self) {
      return new HiPay({
        username: self.apiUsernameTokenJs,
        password: self.apiPasswordTokenJs,
        environment: self.env,
        lang: self.locale.length > 2 ? self.locale.substr(0, 2) : "en",
      });
    },

    isApplePayVisible: function () {
      return canMakeApplePay();
    },

    isApplePayAllowed: function () {
      var self = this;

      if (self.instanceApplePay) {
        return true;
      }

      if (!self.displayName) {
        return false;
      }

      if (self.merchantId) {
        var hipaySdk = self.initHostedFields(self);

        hipaySdk
          .canMakePaymentsWithActiveCard(self.merchantId)
          .then(function (canMakePayments) {
            if (canMakePayments) {
              self.initApplePayField(self, hipaySdk);
            }
          });
        // Mandatory
        return true;
      } else {
        var canMakePayments = false;
        try {
          canMakePayments = window.ApplePaySession.canMakePayments();
        } catch (e) {
          return false;
        }
        if (canMakePayments) {
          return self.initApplePayField(self);
        }
      }
    },

    initTOCEvents: function() {
      var self = this;
      
      $(document).ready(function() {
        if (window.checkoutConfig.checkoutAgreements.isEnabled) {
          $('body').on('DOMNodeInserted', function(e) {
            var results = document.querySelectorAll("input[id*='agreement_hipay_applepay']")
            var agreements = window.checkoutConfig.checkoutAgreements.agreements;
            agreements = agreements.filter((input) => input.mode == '1');
              if(results.length && results.length == agreements.length){
                results.forEach(function(input, index) {
                  self.allTOC.set(index, false);
                  input.addEventListener('change', function(event){
                    self.allTOC.set(index, event.target.checked);
                    updateTOCState();
                  });
                })
                $('body').off('DOMNodeInserted');
              }
          });
        }

        function updateTOCState(){
          console.log('updateTOCState');
          var noChecked = [...self.allTOC.values()].filter((value) => value == false);
          if(noChecked.length > 0){
            self.isAllTOCChecked(false);
          }else{
            self.isAllTOCChecked(true);
          }
        }
      });
    },

    initApplePayField: function (self, hipaySdk) {
      if (!hipaySdk) {
        hipaySdk = self.initHostedFields(self);
      }

      var applePayConfig = {
        displayName: self.displayName,
        request: {
          countryCode: quote.billingAddress().countryId,
          currencyCode: quote.totals().quote_currency_code,
          total: {
            label: self.displayName,
            amount: Number(quote.totals().base_grand_total).toFixed(2),
          },
        },
        selector: "hipay-apple-pay-button",
        applePayStyle: {
          type: self.buttonType,
          color: self.buttonColor,
        },
      };

      self.instanceApplePay = hipaySdk.create(
        "paymentRequestButton",
        applePayConfig
      );

      self.initTOCEvents();

      if (self.instanceApplePay) {
        canMakeApplePay(true);

        self.totals.subscribe(function (newValue) {
          if (
            applePayConfig.request.total.amount != newValue.base_grand_total
          ) {
            applePayConfig.request.total.amount = Number(
              newValue.base_grand_total
            ).toFixed(2);
            self.instanceApplePay.update(applePayConfig);
          }
        });

        self.instanceApplePay.on("paymentAuthorized", function (token) {
          self.paymentAuthorized(self, token);
        });

        return true;
      }
    },

    paymentAuthorized: function (self, tokenHipay) {
      var body = $("body");
      self.creditCardToken(tokenHipay.token);
      self.creditCardType(
        tokenHipay.brand.toLowerCase().replace(/ /g, "-") || self.creditCardType
      );

      var deferred = $.Deferred();
      $.when(placeOrderAction(self.getData(), self.messageContainer))
        .fail(function (error) {
          deferred.reject(Error(error));
          self.instanceApplePay.completePaymentWithFailure();
        })
        .done(function () {
          deferred.resolve(true);
          storage
            .get(self.placeOrderStatusUrl)
            .done(function (response) {
              if (response) {
                if (response.redirectUrl && response.statusOK === true) {
                  self.instanceApplePay.completePaymentWithSuccess();
                } else {
                  self.instanceApplePay.completePaymentWithFailure();
                }
                if (response.redirectUrl) {
                  $.mage.redirect(response.redirectUrl);
                }
              }
            })
            .fail(function () {
              self.instanceApplePay.completePaymentWithFailure();
              $.mage.redirect(self.afterPlaceOrderUrl);
            });
        });
      body.loader("hide");
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
      this._super().observe(["creditCardToken", "creditCardType", "eci"]);

      return this;
    },

    /**
     * @returns {Boolean}
     */
    isShowLegend: function () {
      return true;
    },

    context: function () {
      return this;
    },

    /**
     * @override
     */
    getCode: function () {
      return "hipay_applepay";
    },

    getData: function () {
      return {
        method: this.item.method,
        additional_data: {
          card_token: this.creditCardToken(),
          eci: this.eci(),
          cc_type: this.creditCardType(),
        },
      };
    },
  });
});
