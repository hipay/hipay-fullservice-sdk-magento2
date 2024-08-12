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
  'HiPay_FullserviceMagento/js/view/payment/cc-form',
  'Magento_Checkout/js/model/full-screen-loader',
  'Magento_Checkout/js/model/quote',
  'domReady!'
], function (ko, $, Component, fullScreenLoader, quote) {
  'use strict';

  return Component.extend({
    createHostedFields: function (context) {
      var self = this;
      if (context) {
        self = context;
      }
      self.hipayHostedFields = self.hipaySdk.create('card', self.configHipay);

      self.hipayHostedFields.on('change', function (data) {
        if (self.showCcForm()) {
          if (!data.valid || data.error) {
            self.hipayHFstatus = false;
          } else if (data.valid) {
            self.hipayHFstatus = true;
          }
          self.isPlaceOrderAllowed(self.hipayHFstatus);
        }
      });

      self.hipaySdk.injectBaseStylesheet();

      self.hipayHostedFields.on('blur', function (data) {
        // Get error container
        var domElement = document.querySelector(
          "[data-hipay-id='hipay-card-field-error-" + data.element + "']"
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
          "[data-hipay-id='hipay-card-field-error-" + data.element + "']"
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
        self.initHiPayConfiguration(self.createHostedFields);
      } else {
        self.createHostedFields();
      }
      return true;
    },

    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-hosted-fields',
      showCcForm: true,
      env:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.env
          : '',
      apiUsernameTokenJs:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.apiUsernameTokenJs
          : '',
      apiPasswordTokenJs:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.apiPasswordTokenJs
          : '',
      color:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.color
          : '',
      fontFamily:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.fontFamily
          : '',
      fontSize:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.fontSize
          : '',
      fontWeight:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.fontWeight
          : '',
      placeholderColor:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.placeholderColor
          : '',
      caretColor:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.caretColor
          : '',
      iconColor:
        window.checkoutConfig.payment.hipay_hosted_fields !== undefined
          ? window.checkoutConfig.payment.hipay_hosted_fields.iconColor
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
      hipaySdk: ''
    },

    hipayHostedFields: null,
    configHipay: null,
    hipayHFstatus: false,
    isPlaceOrderAllowed: ko.observable(false),
    isAllTOCChecked: ko.observable(
      !(
        window.checkoutConfig.checkoutAgreements.isEnabled &&
        window.checkoutConfig.checkoutAgreements.agreements.some(
          (input) => input.mode == '1'
        )
      )
    ),
    allTOC: new Map(),

    /**
     * @param {Function} handler
     */
    setValidateHandler: function (handler) {
      this.validateHandler = handler;
    },

    allowMultiUse: function () {
      var self = this;
      return self.allowOneclick.hipay_hosted_fields && self.createOneclick();
    },

    changeOneClick: function () {
      var self = this;
      self.hipayHostedFields.setMultiUse(self.allowMultiUse());
    },

    changeCard: function () {
      var self = this;
      if (!self.showCcForm()) {
        self.isPlaceOrderAllowed(true);
      } else {
        self.isPlaceOrderAllowed(self.hipayHFstatus);
      }
    },

    initTOCEvents: function () {
      var self = this;

      $(document).ready(function () {
        if (window.checkoutConfig.checkoutAgreements.isEnabled) {
          var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
              if (mutation.type === 'childList' && mutation.addedNodes.length) {
                initHostedFieldsEvents();
              }
            });
          });
          observer.observe(document.body, { childList: true, subtree: true });
        }

        function initHostedFieldsEvents() {
          var results = document.querySelectorAll(
            "input[id*='agreement_hipay_hosted_fields']"
          );
          var agreements = window.checkoutConfig.checkoutAgreements.agreements;
          agreements = agreements.filter((input) => input.mode == '1');
          if (results.length && results.length == agreements.length) {
            results.forEach(function (input, index) {
              self.allTOC.set(index, false);
              input.addEventListener('change', function (event) {
                self.allTOC.set(index, event.target.checked);
                updateTOCState();
              });
            });
            observer.takeRecords();
          }
        }

        function updateTOCState() {
          var noChecked = [...self.allTOC.values()].filter(
            (value) => value == false
          );
          if (noChecked.length > 0) {
            self.isAllTOCChecked(false);
          } else {
            self.isAllTOCChecked(true);
          }
        }
      });
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
        selector: 'hipay-container-hosted-fields',
        multi_use: self.allowMultiUse(),
        fields: {
          cardHolder: {
            selector: 'hipay-card-holder',
            defaultFirstname: customerFirstName,
            defaultLastname: customerLastName
          },
          cardNumber: {
            selector: 'hipay-card-number'
          },
          expiryDate: {
            selector: 'hipay-date-expiry'
          },
          cvc: {
            selector: 'hipay-cvc',
            helpButton: true,
            helpSelector: 'hipay-help-cvc'
          }
        },
        styles: {
          base: {
            fontFamily: self.fontFamily,
            color: self.color,
            fontSize: self.fontSize,
            fontWeight: self.fontWeight,
            placeholderColor: self.placeholderColor,
            caretColor: self.caretColor,
            iconColor: self.iconColor
          }
        }
      };

      self.initTOCEvents();
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
      self._super().observe(['createOneclick']);

      self.showCcForm = ko.computed(function () {
        var showCC =
          !(self.useOneclick() && self.customerHasCard()) ||
          self.selectedCard() === undefined ||
          self.selectedCard() === '';
        return showCC;
      }, self);

      return self;
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

      if (self.creditCardToken()) {
        self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
        return;
      }

      fullScreenLoader.startLoader();
      self.hipayHostedFields.getPaymentData().then(
        function (response) {
          self.creditCardToken(response.token);
          self.creditCardType(response.payment_product);
          self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
          self.creditCardToken('');
          fullScreenLoader.stopLoader();
        },
        function (errors) {
          for (var error in errors) {
            var domElement = document.querySelector(
              "[data-hipay-id='hipay-card-field-error-" +
                errors[error].field +
                "']"
            );

            // If DOM element add error inside
            if (domElement) {
              domElement.innerText = errors[error].error;
            }
          }
          fullScreenLoader.stopLoader();
        }
      );
    }
  });
});
