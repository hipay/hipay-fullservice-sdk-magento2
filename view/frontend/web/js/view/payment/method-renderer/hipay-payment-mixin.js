/**
 * HiPay Fullservice Magento - Payment Method Mixin
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
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/model/full-screen-loader'
], function ($, quote, fullScreenLoader) {
  'use strict';

  return function (target) {
    return target.extend({
      /**
       * Check if this payment method is active
       */
      isPaymentMethodActive: function () {
        var self = this;
        var selectedPaymentMethod = quote.paymentMethod();
        return selectedPaymentMethod?.method === self.getCode();
      },

      /**
       * Initialize mini cart change listeners for HiPay payment methods
       */
      initMiniCartListener: function () {
        var self = this;

        // Listen for mini cart updates (quantity changes, product removal, etc.)
        $(document).ready(function () {
          // Listen for mini cart updates via AJAX (more comprehensive)
          $(document).ajaxComplete(function (event, xhr, settings) {
            // Check if the AJAX call is related to mini cart updates
            if (
              settings.url?.includes('/updateItemQty') ||
              settings.url?.includes('/removeItem')
            ) {
              if (self.isPaymentMethodActive()) {
                setTimeout(function () {
                  self.handleMiniCartChange();
                }, 1000); // Longer delay for AJAX operations
              }
            }
          });
        });
      },

      /**
       * Handle mini cart changes by reloading the page
       */
      handleMiniCartChange: function () {
        var self = this;

        // Only proceed if this payment method is still active
        if (!self.isPaymentMethodActive()) {
          return;
        }

        // Show loading indicator before reload
        fullScreenLoader.startLoader();

        // Reload the page
        window.location.reload();
      },

      /**
       * Safe number formatting with proper rounding
       */
      safeToFixed: function (value, decimals = 2) {
        const factor = 10 ** decimals;
        const correction = 1 / 10 ** (decimals + 2);
        const rounded = Math.round((value + correction) * factor) / factor;
        return rounded.toFixed(decimals);
      },

      /**
       * Get country code with fallback logic
       * Priority: billing address -> shipping address -> store default
       * @returns {string} Country code
       */
      getCountryCodeWithFallback: function () {
        var billingAddress = quote.billingAddress();
        var shippingAddress = quote.shippingAddress();
        
        // Try billing address first, then shipping, then store default, then FR
        return billingAddress?.countryId || 
               shippingAddress?.countryId || 
               window.checkoutConfig?.storeConfig?.defaultCountryId || 
               'FR';
      },

      /**
       * Get billing address with fallback to shipping address
       * @returns {Object|null} Address object
       */
      getAddressWithFallback: function () {
        const addresses = [quote.billingAddress(), quote.shippingAddress()];
        return addresses.find(addr => addr?.firstname && addr?.lastname) || null;
      }
    });
  };
});
