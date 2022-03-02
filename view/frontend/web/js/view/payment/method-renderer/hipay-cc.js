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
  'mage/storage',
  'Magento_Checkout/js/model/full-screen-loader',
  'mage/translate'
], function (ko, $, Component, storage, fullScreenLoader, $t) {
  'use strict';
  return Component.extend({
    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-cc',
      showCcForm: true,
      env:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.env
          : '',
      apiUsernameTokenJs:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.apiUsernameTokenJs
          : '',
      apiPasswordTokenJs:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.apiPasswordTokenJs
          : '',
      icons:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.icons
          : '',
      allowOneclick:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.allowOneclick
          : false,
      sdkJsUrl:
        window.checkoutConfig.payment.hipay_cc !== undefined
          ? window.checkoutConfig.payment.hipay_cc.sdkJsUrl
          : ''
    },

    placeOrderHandler: null,
    validateHandler: null,

    initialize: function () {
      var self = this;
      this._super();
    },

    getIcons: function (type) {
      return this.icons.hasOwnProperty(type) ? this.icons[type] : false;
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
    /**
     * @override
     */
    initObservable: function () {
      var self = this;
      this._super();

      this.showCcForm = ko.computed(function () {
        var showCC =
          !(self.useOneclick() && self.customerHasCard()) ||
          self.selectedCard() === undefined ||
          self.selectedCard() === '';

        self.showCVV(showCC);
        return showCC;
      }, this);

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
    hasSsCardType: function () {
      return false;
    },
    getCcAvailableTypes: function () {
      return window.checkoutConfig.payment.hipay_cc.availableTypes;
    },

    /**
     * @override
     */
    getCode: function () {
      return 'hipay_cc';
    },
    getData: function () {
      return this._super();
    },
    /**
     * Display error message
     *
     * @param {*} error - error message
     */
    addError: function (error) {
      this.creditCardToken('');
      if (_.isObject(error)) {
        this.messageContainer.addErrorMessage(error);
      } else {
        this.messageContainer.addErrorMessage({
          message: error
        });
      }
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

      if (this.validateHandler()) {
        if (this.creditCardToken()) {
          self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
          return;
        }

        fullScreenLoader.startLoader();
        var params = {
          cardNumber: this.creditCardNumber(),
          cvc: this.creditCardVerificationNumber(),
          expiryMonth: this.creditCardExpMonth().padStart(2, '0'),
          expiryYear: this.creditCardExpYear().substr(-2),
          cardHolder: this.creditCardOwner(),
          multiUse: this.createOneclick()
        };

        this.hipaySdk.tokenize(params).then(
          function (response) {
            self.creditCardToken(response.token);
            self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
            fullScreenLoader.stopLoader();
          },
          function (error) {
            self.addError(
              $t('An error has occured, please check the information entered.')
            );
            fullScreenLoader.stopLoader();
          }
        );
      }
    }
  });
});
