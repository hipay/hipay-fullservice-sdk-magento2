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
  'jquery',
  'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted',
  'ko',
  'Magento_Checkout/js/view/payment/default',
  'Magento_Checkout/js/model/full-screen-loader',
  'Magento_Checkout/js/model/quote',
  'Magento_Customer/js/customer-data',
  'Magento_Checkout/js/action/get-payment-information',
  'domReady!'
], function (
  $,
  ComponentHosted,
  ko,
  ComponentDefault,
  fullScreenLoader,
  quote,
  customerData,
  getPaymentInformation
) {
  'use strict';

  var isPayPalV2 =
    window.checkoutConfig.payment.hipay_paypalapi.isPayPalV2 ?? '';

  if (isPayPalV2 === 1) {
    return ComponentDefault.extend({
      defaults: {
        template: 'HiPay_FullserviceMagento/payment/hipay-paypal',
        configHipay: null,
        hipayHostedFields: null,
        redirectAfterPlaceOrder: false,
        totals: quote.totals,
        placeOrderStatusUrl:
          window.checkoutConfig.payment.hiPayFullservice.placeOrderStatusUrl
            .hipay_paypalapi,
        afterPlaceOrderUrl:
          window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
            .hipay_paypalapi,
        env: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.env
          : 'stage',
        apiUsernameTokenJs: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.apiUsernameTokenJs
          : '',
        apiPasswordTokenJs: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.apiPasswordTokenJs
          : '',
        buttonLabel: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.button_label
          : 'pay',
        buttonShape: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.button_shape
          : 'pill',
        buttonColor: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.button_color
          : 'gold',
        buttonHeight: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.button_height
          : '40',
        bnpl: window.checkoutConfig.payment.hipay_paypalapi
          ? window.checkoutConfig.payment.hipay_paypalapi.bnpl
          : true,
        locale: window.checkoutConfig.payment.hiPayFullservice.locale
          ? window.checkoutConfig.payment.hiPayFullservice.locale
              .hipay_paypalapi
          : 'en_us'
      },

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
      cartChangeTimeout: null,

      initialize: function () {
        var self = this;
        self._super();

        self.configHipay = {
          template: 'auto',
          selector: 'paypal-field',
          canPayLater: Boolean(self.bnpl),
          paypalButtonStyle: {
            shape: self.buttonShape,
            height: Number(self.buttonHeight),
            color: self.buttonColor,
            label: self.buttonLabel
          },
          request: {
            amount: self.safeToFixed(Number(quote.totals().base_grand_total)),
            currency: quote.totals().quote_currency_code,
            locale: this.convertToUpperCaseAfterUnderscore(self.locale)
          }
        };

        // Subscribe to quote totals changes
        quote.totals.subscribe(function (totals) {
          if (self.hipayHostedFields && totals) {
            self.updatePayPalAmount(totals);
          }
        });

        // Subscribe to cart section changes
        self.initCartChangeListener();

        // Listen for DOM changes to handle PayPal field recreation
        self.initPayPalFieldObserver();

        self.initTOCEvents();
      },

      /**
       * Initialize cart change listener
       */
      initCartChangeListener: function () {
        var self = this;
        var cartData = customerData.get('cart');

        cartData.subscribe(function (updatedCart) {
          if (updatedCart?.items) {
            // Only handle cart changes if PayPal is the active payment method
            if (self.isPayPalActive()) {
              self.handleCartChange();
            }
          }
        });

        // Listen for discount/coupon changes
        self.initDiscountListener();
      },

      /**
       * Initialize PayPal field observer to handle DOM recreation
       */
      initPayPalFieldObserver: function () {
        var self = this;
        
        // Create a mutation observer to watch for PayPal field changes
        if (typeof MutationObserver !== 'undefined') {
          var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
              if (mutation.type === 'childList') {
                var paypalField = document.getElementById('paypal-field');
                if (paypalField?.children.length > 0 && !self.hipayHostedFields) {
                  // PayPal field was recreated but we don't have an instance
                  setTimeout(function() {
                    self.initHostedFields();
                  }, 100);
                }
              }
            });
          });
          
          // Start observing the PayPal field container
          var paypalField = document.getElementById('paypal-field');
          if (paypalField) {
            observer.observe(paypalField, {
              childList: true,
              subtree: true
            });
          }
        }
      },

      /**
       * Initialize discount and coupon change listeners
       */
      initDiscountListener: function () {
        var self = this;

        // Listen for coupon code application/removal
        $(document).on('click', '[data-action="apply-coupon"], [data-action="cancel-coupon"]', function() {
          if (self.isPayPalActive()) {
            setTimeout(function() {
              self.handleCartChange();
            }, 500); // Wait for coupon action to complete
          }
        });

        // Listen for discount code input changes
        $(document).on('input', 'input[name="coupon_code"], input[name="discount_code"]', function() {
          if (self.isPayPalActive()) {
            self.handleCartChange();
          }
        });

        // Listen for AJAX completion (covers most discount/coupon operations)
        $(document).on('ajaxComplete', function(event, xhr, settings) {
          // Check if the AJAX request was related to discount/coupon
          if (settings.url && (
            settings.url.includes('coupon') || 
            settings.url.includes('discount') || 
            settings.url.includes('cart') ||
            settings.url.includes('checkout')
          )) {
            if (self.isPayPalActive()) {
              setTimeout(function() {
                self.handleCartChange();
              }, 300);
            }
          }
        });

        // Listen for quote totals changes (covers all price changes)
        quote.totals.subscribe(function(totals) {
          if (self.isPayPalActive() && totals) {
            self.updatePayPalAmount(totals);
          }
        }, null, 'change');

        // Listen for billing address changes (can affect totals)
        quote.billingAddress.subscribe(function(address) {
          if (self.isPayPalActive() && address) {
            setTimeout(function() {
              self.handleCartChange();
            }, 500);
          }
        });

        // Listen for shipping method changes
        quote.shippingMethod.subscribe(function(method) {
          if (self.isPayPalActive() && method) {
            setTimeout(function() {
              self.handleCartChange();
            }, 500);
          }
        });
      },

      /**
       * Handle cart changes with debouncing
       */
      handleCartChange: function () {
        var self = this;

        // Clear existing timeout
        if (self.cartChangeTimeout) {
          clearTimeout(self.cartChangeTimeout);
        }

        // Debounce the cart change handling
        self.cartChangeTimeout = setTimeout(function () {
          self.refreshPaymentData();
        }, 500);
      },

      /**
       * Refresh payment data when cart changes
       */
      refreshPaymentData: function () {
        var self = this;

        // Show loading
        fullScreenLoader.startLoader();

        // Get fresh payment information
        getPaymentInformation()
          .then(function (data) {
            if (data.totals) {
              // Update quote totals
              quote.setTotals(data.totals);

              // Update PayPal configuration
              self.updatePayPalConfiguration(data.totals);

              // Reinitialize PayPal if needed
              if (self.hipayHostedFields) {
                self.reinitializePayPal();
              }
            }
          })
          .catch(function(error) {
            console.error('Error refreshing payment data:', error);
          })
          .always(function () {
            fullScreenLoader.stopLoader();
          });
      },

      /**
       * Update PayPal configuration with new totals
       */
      updatePayPalConfiguration: function (totals) {
        var self = this;

        self.configHipay.request.amount = self.safeToFixed(
          Number(totals.base_grand_total)
        );
        self.configHipay.request.currency = totals.quote_currency_code;
      },

      /**
       * Update PayPal amount (for existing instances)
       */
      updatePayPalAmount: function (totals) {
        var self = this;

        if (self.hipayHostedFields && totals) {
          try {
            var newAmount = self.safeToFixed(Number(totals.base_grand_total));
            
            // Update the amount if PayPal SDK supports it
            if (self.hipayHostedFields.updateAmount) {
              self.hipayHostedFields.updateAmount(newAmount);
              // Also update the configuration for consistency
              self.configHipay.request.amount = newAmount;
              self.configHipay.request.currency = totals.quote_currency_code;
            } else {
              // Fallback: reinitialize PayPal
              self.reinitializePayPal();
            }
          } catch (error) {
            console.warn('PayPal amount update failed:', error);
            self.reinitializePayPal();
          }
        }
      },

      /**
       * Reinitialize PayPal hosted fields
       */
      reinitializePayPal: function () {
        var self = this;

        // Destroy existing instance
        if (self.hipayHostedFields) {
          try {
            self.hipayHostedFields.destroy();
          } catch (error) {
            console.warn('PayPal destroy failed:', error);
          }
          self.hipayHostedFields = null;
        }

        // Clear the DOM element completely
        var paypalField = document.getElementById('paypal-field');
        if (paypalField) {
          paypalField.innerHTML = '';
          // Remove any existing PayPal iframes or elements
          var paypalElements = paypalField.querySelectorAll('*');
          paypalElements.forEach(function(element) {
            element.remove();
          });
        }

        // Reset state
        self.isPlaceOrderAllowed(false);

        // Reinitialize after a short delay to ensure DOM is cleared
        setTimeout(function () {
          self.initHostedFields();
        }, 200);
      },

      initHostedFields: function () {
        var self = this;

        // Ensure the DOM element is clean before creating new instance
        var paypalField = document.getElementById('paypal-field');
        if (paypalField?.children.length > 0) {
          paypalField.innerHTML = '';
        }

        // Create new HiPay SDK instance
        self.hipaySdk = new HiPay({
          username: self.apiUsernameTokenJs,
          password: self.apiPasswordTokenJs,
          environment: self.env,
          lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
        });

        // Create PayPal hosted fields
        self.hipayHostedFields = self.hipaySdk.create(
          'paypal',
          self.configHipay
        );

        self.hipayHostedFields.on('paymentAuthorized', function (token) {
          self.paymentAuthorized(self, token);
        });

        self.isPlaceOrderAllowed(true);

        return true;
      },

      initTOCEvents: function () {
        var self = this;

        $(document).ready(function () {
          if (window.checkoutConfig.checkoutAgreements.isEnabled) {
            var observer = new MutationObserver(function (mutations) {
              mutations.forEach(function (mutation) {
                if (
                  mutation.type === 'childList' &&
                  mutation.addedNodes.length
                ) {
                  initPaypalEvents();
                }
              });
            });
            observer.observe(document.body, { childList: true, subtree: true });
          }

          function initPaypalEvents() {
            var results = document.querySelectorAll(
              "input[id*='agreement_hipay_paypal']"
            );
            var agreements =
              window.checkoutConfig.checkoutAgreements.agreements;
            agreements = agreements.filter((input) => input.mode == '1');
            if (results?.length == agreements.length) {
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

      paymentAuthorized: function (self, tokenHipay) {
        self.browser_info(JSON.stringify(tokenHipay.browser_info));
        self.paypal_order_id(tokenHipay.orderID);
        self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
      },

      isPaypalV2Allowed: function () {
        return isPayPalV2 === 1;
      },

      /**
       * Check if PayPal is the active payment method
       */
      isPayPalActive: function () {
        var self = this;
        var selectedPaymentMethod = quote.paymentMethod();

        // Check if PayPal is selected as payment method
        return (
          selectedPaymentMethod?.method === self.getCode()
        );
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
        self._super().observe(['paypal_order_id', 'browser_info']);
        return self;
      },

      afterPlaceOrder: function () {
        $.mage.redirect(this.afterPlaceOrderUrl);
      },

      context: function () {
        return this;
      },

      getProductCode: function () {
        return 'paypal';
      },

      getCode: function () {
        return 'hipay_paypalapi';
      },

      getData: function () {
        var self = this;
        var parent = self._super();
        var data = {
          method: self.item.method,
          additional_data: {
            paypal_order_id: self.paypal_order_id(),
            browser_info: self.browser_info()
          }
        };
        return $.extend(true, parent, data);
      },

      isActive: function () {
        return true;
      },

      convertToUpperCaseAfterUnderscore: function (str) {
        let parts = str.split('_');
        parts[1] = parts[1].toUpperCase();
        return parts.join('_');
      },

      safeToFixed: function (value, decimals = 2) {
        const factor = 10 ** decimals;
        const correction = 1 / 10 ** (decimals + 2);
        const rounded = Math.round((value + correction) * factor) / factor;
        return rounded.toFixed(decimals);
      },

      /**
       * Cleanup method to clear timeouts and PayPal instances
       */
      dispose: function () {
        var self = this;
        
        // Clear timeout
        if (self.cartChangeTimeout) {
          clearTimeout(self.cartChangeTimeout);
        }
        
        // Destroy PayPal instance
        if (self.hipayHostedFields) {
          try {
            self.hipayHostedFields.destroy();
            self.hipayHostedFields = null;
          } catch (error) {
            console.warn('PayPal destroy failed during dispose:', error);
          }
        }
        
        // Clear DOM element
        var paypalField = document.getElementById('paypal-field');
        if (paypalField) {
          paypalField.innerHTML = '';
        }
        
        self._super();
      }
    });
  } else {
    return ComponentHosted.extend({
      defaults: {
        template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
        redirectAfterPlaceOrder: false
      },

      getCode: function () {
        return 'hipay_paypalapi';
      },

      isActive: function () {
        return true;
      }
    });
  }
});
