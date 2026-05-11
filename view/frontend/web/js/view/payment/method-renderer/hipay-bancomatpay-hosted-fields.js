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
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
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
          .hipay_bancomatpay_hosted_fields,
      template:
        'HiPay_FullserviceMagento/payment/hipay-bancomatpay-hosted-fields',
      env: window.checkoutConfig.payment.hipay_bancomatpay_hosted_fields
        ? window.checkoutConfig.payment.hipay_bancomatpay_hosted_fields.env
        : 'stage',
      apiUsernameTokenJs: window.checkoutConfig.payment
        .hipay_bancomatpay_hosted_fields
        ? window.checkoutConfig.payment.hipay_bancomatpay_hosted_fields
            .apiUsernameTokenJs
        : '',
      apiPasswordTokenJs: window.checkoutConfig.payment
        .hipay_bancomatpay_hosted_fields
        ? window.checkoutConfig.payment.hipay_bancomatpay_hosted_fields
            .apiPasswordTokenJs
        : '',
      locale: window.checkoutConfig.payment.hiPayFullservice.locale
        ? window.checkoutConfig.payment.hiPayFullservice.locale
            .hipay_bancomatpay_hosted_fields
        : 'en_us'
    },
    isPlaceOrderAllowed: ko.observable(false),
    isAllTOCChecked: ko.observable(
      !(
        window.checkoutConfig.checkoutAgreements.isEnabled &&
        window.checkoutConfig.checkoutAgreements.agreements.some(function (
          agreement
        ) {
          return agreement.mode == '1';
        })
      )
    ),
    isPhoneNumberValid: ko.observable(false),
    hasPhoneInteraction: ko.observable(false),
    allTOC: new Map(),

    initialize: function () {
      var self = this;
      self._super();

      self.configHipay = {
        selector: `hipay-container-hosted-fields-${self.getProductCode()}`,
        template: 'auto'
      };

      self.initTOCEvents();
    },

    hasMandatoryAgreements: function () {
      return Boolean(
        window.checkoutConfig.checkoutAgreements.isEnabled &&
          window.checkoutConfig.checkoutAgreements.agreements.some(function (
            agreement
          ) {
            return agreement.mode == '1';
          })
      );
    },

    initHostedFields: function () {
      var self = this;

      if (self.hipayHostedFields) {
        return true;
      }

      self.hipaySdk = new HiPay({
        username: self.apiUsernameTokenJs,
        password: self.apiPasswordTokenJs,
        environment: self.env,
        lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
      });

      self.hipayHostedFields = self.hipaySdk.create(
        'bancomatpay',
        self.configHipay
      );

      self.hipayHostedFields.on('change', function (data) {
        self.updatePhoneFieldState(data, false);
      });

      self.hipayHostedFields.on('inputChange', function (data) {
        self.updatePhoneFieldState(data, true);
      });

      self.hipayHostedFields.on('blur', function (data) {
        self.updatePhoneFieldState(data, true);
      });

      self.isPlaceOrderAllowed(false);

      return true;
    },

    shouldDisplayHostedFields: function () {
      if (this.hasMandatoryAgreements() && !this.isAllTOCChecked()) {
        return false;
      }

      return this.initHostedFields();
    },

    shouldDisplayInformativeMessage: function () {
      return this.hasMandatoryAgreements();
    },

    isHostedFieldsPlaceOrderAllowed: function () {
      return (
        (!this.hasMandatoryAgreements() || this.isAllTOCChecked()) &&
        this.isPlaceOrderAllowed()
      );
    },

    initTOCEvents: function () {
      var self = this;

      $(document).ready(function () {
        if (!self.hasMandatoryAgreements()) {
          return;
        }

        var observer = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
              initBancomatPayEvents();
            }
          });
        });

        observer.observe(document.body, {
          childList: true,
          subtree: true
        });

        function initBancomatPayEvents() {
          var results = document.querySelectorAll(
            "input[id*='agreement_hipay_bancomatpay']"
          );

          if (results.length === 0) {
            results = document.querySelectorAll(
              '.checkout-agreements input[type="checkbox"][id*="agreement"]'
            );
          }

          var agreements = window.checkoutConfig.checkoutAgreements.agreements;
          agreements = agreements.filter(function (agreement) {
            return agreement.mode == '1';
          });

          if (results.length && results.length === agreements.length) {
            results.forEach(function (input, index) {
              self.allTOC.set(index, input.checked);
              input.addEventListener('change', function (event) {
                self.allTOC.set(index, event.target.checked);
                updateTOCState();
              });
            });

            updateTOCState();
            observer.takeRecords();
          }
        }

        function updateTOCState() {
          var allChecked = Array.from(self.allTOC.values()).every(function (
            value
          ) {
            return value === true;
          });

          self.isAllTOCChecked(allChecked);
        }
      });
    },

    updatePhoneFieldState: function (data, markInteraction) {
      if (markInteraction) {
        this.hasPhoneInteraction(true);
      }

      var error = this.extractPhoneFieldError(data);
      var valid = this.isPhoneFieldValid(data);

      this.isPhoneNumberValid(valid);
      this.isPlaceOrderAllowed(valid);
    },

    isPhoneFieldValid: function (data) {
      if (!data) {
        return false;
      }

      if (typeof data.valid === 'boolean') {
        return data.valid === true && !this.extractPhoneFieldError(data);
      }

      if (data.validity) {
        return data.validity.valid === true;
      }

      return false;
    },

    extractPhoneFieldError: function (data) {
      if (!data) {
        return '';
      }

      if (!this.hasPhoneInteraction()) {
        return '';
      }

      if (data.validity) {
        if (data.validity.empty) {
          return '';
        }

        if (data.validity.valid === false) {
          return data.validity.error || '';
        }
      }

      if (data.valid === false) {
        return data.error || '';
      }

      return '';
    },

    getBancomatInformativeMessage: function () {
      return $.mage.__(
        'The payment will have to be validated on your Bancomat Pay application.'
      );
    },

    setPlaceOrderHandler: function (handler) {
      this.placeOrderHandler = handler;
    },

    setValidateHandler: function (handler) {
      this.validateHandler = handler;
    },

    initObservable: function () {
      var self = this;
      self._super().observe(['creditCardType', 'phone', 'browser_info']);

      return self;
    },

    place_order: function (data, event) {
      var self = this;
      if (event) {
        event.preventDefault();
      }

      if (!self.isHostedFieldsPlaceOrderAllowed()) {
        return false;
      }

      fullScreenLoader.startLoader();
      self.hipayHostedFields.getPaymentData().then(
        function (response) {
          self.creditCardType(response.payment_product);
          self.phone(response.phone);
          self.browser_info(JSON.stringify(response.browser_info));
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
      return 'bancomatpay';
    },

    getCode: function () {
      return 'hipay_bancomatpay_hosted_fields';
    },

    getData: function () {
      var self = this;
      var parent = self._super();
      var data = {
        method: self.item.method,
        additional_data: {
          cc_type: self.creditCardType(),
          browser_info: self.browser_info(),
          phone: self.phone()
        }
      };

      return $.extend(true, parent, data);
    },

    isActive: function () {
      return true;
    }
  });
});
