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
define(['jquery', 'Magento_Checkout/js/view/payment/default'], function (
  $,
  Component
) {
  'use strict';
  return Component.extend({
    defaults: {
      configHipay: null,
      hipayHostedFields: null,
      redirectAfterPlaceOrder: false,
      afterPlaceOrderUrl:
        window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
          .hipay_ideal_hosted_fields,
      template: 'HiPay_FullserviceMagento/payment/hipay-hosted-fields-local',
      env: window.checkoutConfig.payment.hipay_ideal_hosted_fields
        ? window.checkoutConfig.payment.hipay_ideal_hosted_fields.env
        : 'stage',
      apiUsernameTokenJs: window.checkoutConfig.payment
        .hipay_ideal_hosted_fields
        ? window.checkoutConfig.payment.hipay_ideal_hosted_fields
            .apiUsernameTokenJs
        : '',
      apiPasswordTokenJs: window.checkoutConfig.payment
        .hipay_ideal_hosted_fields
        ? window.checkoutConfig.payment.hipay_ideal_hosted_fields
            .apiPasswordTokenJs
        : '',
      locale: window.checkoutConfig.payment.hiPayFullservice.locale
        ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_applepay
        : 'en_us'
    },

    initialize: function () {
      var self = this;
      self._super();

      self.configHipay = {
        selector: 'hipay-container-hosted-fields-local',
        template: 'auto'
      };
    },

    initHostedFields: function () {
      var self = this;

      self.hipaySdk = HiPay({
        username: self.apiUsernameTokenJs,
        password: self.apiPasswordTokenJs,
        environment: self.env,
        lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
      });

      self.hipayHostedFields = self.hipaySdk.create('ideal', self.configHipay);

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
      self._super().observe(['eci']);

      return self;
    },

    place_order: function (data, event) {
      var self = this;
      if (event) {
        event.preventDefault();
      }

      console.log(data);
      console.log(self.hipayHostedFields);
      console.log(self.getData());
      // self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
    },

    afterPlaceOrder: function () {
      $.mage.redirect(this.afterPlaceOrderUrl);
    },

    context: function () {
      return this;
    },
    getCode: function () {
      return 'hipay_ideal_hosted_fields';
    },
    getData: function () {
      var self = this;
      var parent = self._super();
      var data = {
        method: self.item.method,
        additional_data: {
          browser_info: JSON.stringify(self.hipaySdk.getBrowserInfo())
        }
      };
      return $.extend(true, parent, data);
    },
    isActive: function () {
      return true;
    }
  });
});
