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
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */
define([
  'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted',
  'Magento_Checkout/js/model/quote'
], function (Component, quote) {
  'use strict';
  return Component.extend({
    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
      warnings: window.checkoutConfig.payment.hiPayFullservice.warnings,
      redirectAfterPlaceOrder: false
    },

    getCode: function () {
      return 'hipay_bnpp4X';
    },
    isActive: function () {
      return true;
    },

    getData: function () {
      return {
        method: this.item.method,
        additional_data: {
          cc_type: 'bnpp4x'
        }
      };
    },

    /**
     *  Return warning messages for some provider rules
     *
     * @returns {*}
     */
    getWarningsMessages: function () {
      var billingAddress = quote.billingAddress();
      if (billingAddress) {
        var re = /^((\+|00)33|0)[1-9][0-9]{8}$/;
        if (!re.exec(billingAddress.telephone))
          return 'Please check the phone number entered.';
      }
      return '';
    }
  });
});
