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
  'jquery',
  'ko',
  'HiPay_FullserviceMagento/js/view/payment',
  'HiPay_FullserviceMagento/js/model/iframe',
  'Magento_Checkout/js/model/full-screen-loader',
  'Magento_Checkout/js/model/quote'
], function ($, ko, Component, iframe, fullScreenLoader, quote) {
  'use strict';
  return Component.extend({
    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
      afterPlaceOrderUrl:
        window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
      paymentReady: false,
      creditCardOwner: null
    },
    redirectAfterPlaceOrder: false,
    isInAction: iframe.isInAction,
    placeOrderHandler: null,
    validateHandler: null,

    /**
     *  Return warning messages for some provider rules
     *
     * @returns {*}
     */
    getWarningsMessages: function () {
      return '';
    },
    /**
     *  Get global fingerprint  on dom load of checkout
     *
     * @returns {*}
     */
    getFingerprint: function () {
      if ($('#ioBB')) {
        return $('#ioBB').val();
      } else {
        return '';
      }
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
      this._super().observe('paymentReady');

      return this;
    },
    /**
     * Used in template to load iframe content
     */
    isPaymentReady: function () {
      return this.paymentReady();
    },
    /**
     * After place order callback
     */
    afterPlaceOrder: function () {
      var self = this;

      if (this.isIframeMode() && !this.creditCardToken()) {
        self.paymentReady(true);
      } else {
        $.mage.redirect(this.getAfterPlaceOrderUrl());
      }
    },
    getData: function () {
      var parent = this._super();

      if (this.selectedCard() && this.useOneclick()) {
        var lang = 'en';

        // dirty tricks to get browser information
        // instance of hipaySdk is needed but we don't have credentials in this context
        var hipaySdk = new HiPay({
          username: 'hosted',
          password: 'hosted',
          environment: 'production',
          lang: lang
        });

        var additionalData = {
          additional_data: {
            browser_info: JSON.stringify(hipaySdk.getBrowserInfo())
          }
        };
      }

      return $.extend(true, parent, additionalData);
    },
    getAfterPlaceOrderUrl: function () {
      return this.afterPlaceOrderUrl[this.getCode()];
    },
    context: function () {
      return this;
    },
    getCode: function () {
      return 'hipay_hosted';
    },
    isActive: function () {
      return true;
    },
    isIframeMode: function () {
      return window.checkoutConfig.payment.hiPayFullservice.isIframeMode[
        this.getCode()
      ];
    },
    getIframeWidth: function () {
      return window.checkoutConfig.payment.hiPayFullservice.iFrameWidth[
        this.getCode()
      ];
    },

    getIframeHeight: function () {
      return window.checkoutConfig.payment.hiPayFullservice.iFrameHeight[
        this.getCode()
      ];
    },
    getIframeStyle: function () {
      return window.checkoutConfig.payment.hiPayFullservice.iFrameStyle[
        this.getCode()
      ];
    },
    getIframeWrapperStyle: function () {
      return window.checkoutConfig.payment.hiPayFullservice.iFrameWrapperStyle[
        this.getCode()
      ];
    },
    getIFrameUrl: function () {
      return this.isInAction() ? this.getAfterPlaceOrderUrl() : '';
    },
    /**
     * Places order in pending payment status.
     */
    placePendingPaymentOrder: function () {
      var self = this;
      if (this.placeOrder()) {
        this.isInAction(true);
        // capture all click events
        document.addEventListener('click', iframe.stopEventPropagation, true);
      }
    },
    /**
     * Hide loader when iframe is fully loaded.
     * @returns {void}
     */
    iframeLoaded: function () {
      fullScreenLoader.stopLoader();
    }
  });
});
