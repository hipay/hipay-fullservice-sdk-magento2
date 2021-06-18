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
        if (!data.valid || data.error) {
          self.isPlaceOrderAllowed(false);
        } else if (data.valid) {
          self.isPlaceOrderAllowed(true);
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
    isPlaceOrderAllowed: ko.observable(false),

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
