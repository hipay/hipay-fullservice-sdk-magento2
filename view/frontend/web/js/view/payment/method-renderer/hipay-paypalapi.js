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
  'Magento_Checkout/js/model/quote',
  'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-payment-mixin',
  'domReady!'
], function (
  $,
  ComponentHosted,
  ko,
  ComponentDefault,
  quote,
  hipayPaymentMixin
) {
  'use strict';

  var isPayPalV2 =
    window.checkoutConfig.payment.hipay_paypalapi.isPayPalV2 ?? '';

  if (isPayPalV2 === 1) {
    return hipayPaymentMixin(
      ComponentDefault.extend({
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
        isPayPalVisible: ko.observable(false),
        allTOC: new Map(),

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
              observer.observe(document.body, {
                childList: true,
                subtree: true
              });
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

        /**
         * Initialize discount and coupon change listeners
         */
        initDiscountListener: function () {
          var self = this;

          // Listen for coupon code application/removal
          $(document).on(
            'click',
            '[data-action="apply-coupon"], [data-action="cancel-coupon"]',
            function () {
              if (self.isPaymentMethodActive()) {
                setTimeout(function () {
                  self.handleDiscountChange();
                }, 500); // Wait for coupon action to complete
              }
            }
          );

          // Listen for discount code input changes with debouncing
          var discountInputTimeout = null;
          $(document).on(
            'input',
            'input[name="coupon_code"], input[name="discount_code"]',
            function () {
              if (self.isPaymentMethodActive()) {
                // Clear existing timeout
                if (discountInputTimeout) {
                  clearTimeout(discountInputTimeout);
                }
                // Debounce the input - only trigger after user stops typing for 1 second
                discountInputTimeout = setTimeout(function () {
                  self.handleDiscountChange();
                }, 1000);
              }
            }
          );
        },

        /**
         * Initialize address change listeners
         */
        initAddressListener: function () {
          var self = this;

          // Listen for billing address changes (can affect totals)
          var lastBillingAddress = null;
          quote.billingAddress.subscribe(function (address) {
            if (self.isPaymentMethodActive() && address) {
              // Only trigger if billing address actually changed (not just initialized)
              var addressKey = address.region_id + '_' + address.country_id;
              if (lastBillingAddress !== addressKey) {
                lastBillingAddress = addressKey;
                setTimeout(function () {
                  self.handleAddressChange();
                }, 500);
              }
            }
          });

          // Listen for shipping method changes
          var lastShippingMethod = null;
          quote.shippingMethod.subscribe(function (method) {
            if (self.isPaymentMethodActive() && method) {
              // Only trigger if shipping method actually changed
              var methodKey = method.carrier_code + '_' + method.method_code;
              if (lastShippingMethod !== methodKey) {
                lastShippingMethod = methodKey;
                setTimeout(function () {
                  self.handleAddressChange();
                }, 500);
              }
            }
          });
        },

        /**
         * Handle discount changes
         */
        handleDiscountChange: function () {
          var self = this;

          // Only proceed if PayPal is still the active payment method
          if (!self.isPaymentMethodActive()) {
            return;
          }

          // Update PayPal amount with current totals
          var currentTotals = quote.totals();
          if (currentTotals && self.hipayHostedFields) {
            self.configHipay.request.amount = self.safeToFixed(
              Number(currentTotals.base_grand_total)
            );
            self.configHipay.request.currency =
              currentTotals.quote_currency_code;
          }
        },

        /**
         * Handle address changes
         */
        handleAddressChange: function () {
          var self = this;

          // Only proceed if PayPal is still the active payment method
          if (!self.isPaymentMethodActive()) {
            return;
          }

          // Update PayPal amount with current totals
          var currentTotals = quote.totals();
          if (currentTotals && self.hipayHostedFields) {
            self.configHipay.request.amount = self.safeToFixed(
              Number(currentTotals.base_grand_total)
            );
            self.configHipay.request.currency =
              currentTotals.quote_currency_code;
          }
        },

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

          quote.totals.subscribe(function (totals) {
            if (self.hipayHostedFields) {
              self.configHipay.request.amount = self.safeToFixed(
                Number(totals.base_grand_total)
              );
            }
          });

          // Listen for discount/coupon changes
          self.initDiscountListener();

          // Listen for address changes
          self.initAddressListener();

          self.initTOCEvents();

          // Initialize mini cart listeners (from mixin)
          self.initMiniCartListener();
        },

        initHostedFields: function () {
          var self = this;

          self.hipaySdk = new HiPay({
            username: self.apiUsernameTokenJs,
            password: self.apiPasswordTokenJs,
            environment: self.env,
            lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
          });

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

        paymentAuthorized: function (self, tokenHipay) {
          self.browser_info(JSON.stringify(tokenHipay.browser_info));
          self.paypal_order_id(tokenHipay.orderID);
          self.placeOrder(self.getData(), self.redirectAfterPlaceOrder);
        },

        isPaypalV2Allowed: function () {
          var self = this;

          if (isPayPalV2 === 1) {
            return true;
          }

          return false;
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

          // Subscribe to quote changes to validate order placement
          quote.billingAddress.subscribe(function () {
            self.handleOrderValidation();
          });

          quote.shippingAddress.subscribe(function () {
            self.handleOrderValidation();
          });

          quote.paymentMethod.subscribe(function () {
            self.handleOrderValidation();
          });

          quote.shippingMethod.subscribe(function () {
            self.handleOrderValidation();
          });

          // Initial validation check
          self.handleOrderValidation();

          return self;
        },

        afterPlaceOrder: function () {
          $.mage.redirect(this.afterPlaceOrderUrl);
        },

        /**
         * Handle order validation for PayPal
         */
        handleOrderValidation: function () {
          var self = this;
          var validation = self.validateOrderPlacement();

          if (validation.canPlace) {
            self.isPayPalVisible(true);
          } else {
            self.isPayPalVisible(false);
          }

          return validation;
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
          // Split the string at the underscore
          let parts = str.split('_');

          // Capitalize the second part
          parts[1] = parts[1].toUpperCase();

          // Join the parts back together
          return parts.join('_');
        }
      })
    );
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
