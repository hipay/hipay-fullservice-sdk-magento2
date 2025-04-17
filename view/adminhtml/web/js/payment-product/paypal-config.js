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
define(['hipayAvailablePaymentProducts', 'domReady!'], function (
    availablePaymentProducts
) {
  'use strict';
  let isV2 = false;
  let isPayPalSelected = false;
  let initPayPalPromise = null;
  let paymentProductsInstance = null;

  function initializePaymentProducts() {
    if (!paymentProductsInstance && typeof hipayConfig !== 'undefined') {
      paymentProductsInstance = availablePaymentProducts();
      paymentProductsInstance.setCredentials(
          hipayConfig.getApiUsernameTokenJs,
          hipayConfig.getApiPasswordTokenJs,
          hipayConfig.getEnv === 'stage'
      );
    }
    return paymentProductsInstance;
  }

  function checkPayPalV2() {
    if (initPayPalPromise !== null && isV2 !== false) {
      return initPayPalPromise;
    }

    initPayPalPromise = new Promise((resolve, reject) => {
      if (
          typeof hipayConfig !== 'undefined' &&
          hipayConfig.getApiUsernameTokenJs &&
          hipayConfig.getApiPasswordTokenJs &&
          typeof hipayConfig.getEnv !== 'undefined'
      ) {
        const instance = initializePaymentProducts();

        // Configure the payment products request for PayPal
        instance.updateConfig('operation', ['4']);
        instance.updateConfig('payment_product', ['paypal']);
        instance.updateConfig('eci', ['7']);
        instance.updateConfig('with_options', true);

        instance
            .getAvailableProducts()
            .then((result) => {
              isV2 =
                  result?.length > 0 &&
                  result[0].options.payer_id.length > 0 &&
                  result[0].options.provider_architecture_version === 'v1';
              resolve(isV2);
            })
            .catch(reject);
      } else {
        resolve(false);
      }
    });

    return initPayPalPromise;
  }

  function toggleFields(shouldEnable) {
    [
      'button_color',
      'button_shape',
      'button_label',
      'button_height',
      'bnpl'
    ].forEach((fieldId) => {
      const hostedField = document.querySelector(
          `[id^="payment_"][id*="_hipay_paypalapi_${fieldId}"]`
      );
      const hostedPageField = document.querySelector(
          `[id^="payment_"][id*="_hipay_hosted_paypal_${fieldId}"]`
      );
      if (hostedField) hostedField.disabled = !shouldEnable;
      if (hostedPageField)
        hostedPageField.disabled = !shouldEnable || !isPayPalSelected;
    });

    const v2StatusRow = document.querySelector(
        '[id^="row_payment_"][id*="_hipay_paypalapi_paypal_v2_status"]'
    );
    const v2StatusRowHostedPage = document.querySelector(
        '[id^="row_payment_"][id*="_hipay_hosted_paypal_v2_status"]'
    );
    if (v2StatusRow) {
      v2StatusRow.style.display =
          shouldEnable && isPayPalSelected ? 'none' : 'table-row';
    }
    if (v2StatusRowHostedPage) {
      v2StatusRowHostedPage.style.display =
          shouldEnable && isPayPalSelected ? 'none' : 'table-row';
    }

    const paypalHostedRow = document.querySelector(
        '[id^="row_payment_"][id*="_hipay_hosted_paypal"]'
    );
    if (paypalHostedRow) {
      paypalHostedRow.style.display = isPayPalSelected ? '' : 'none';
    }
  }

  function checkPayPalSelected() {
    const select = document.querySelector(
        '[id^="payment_"][id*="_hipay_hosted_payment_products"]'
    );
    isPayPalSelected = select
        ? Array.from(select.selectedOptions).some(
            (option) => option.value === 'paypal'
        )
        : false;
    toggleFields(isV2);
  }

  function handleHiPayPayPalSection() {
    const isActive =
        document
            .querySelector('[id^="row_payment_"][id*="_hipay_paypalapi"] .section-config')
            ?.classList.contains('active') || false;
    const isActiveHostedPage =
        document
            .querySelector('[id^="row_payment_"][id*="_hipay_hosted_paypal"] .section-config')
            ?.classList.contains('active') || false;
    if (isActive || isActiveHostedPage) {
      checkPayPalV2()
          .then(() => {
            toggleFields(isV2);
          })
          .catch(() => {
            toggleFields(false);
          });
    } else {
      toggleFields(false);
    }

    checkPayPalSelected();
  }

  const debouncedHandler = debounce(handleHiPayPayPalSection, 250);

  // Initial setup
  handleHiPayPayPalSection();

  // Setup observers and event listeners
  new MutationObserver(debouncedHandler).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class']
  });

  document.addEventListener('click', (event) => {
    if (
        event?.target.id &&
        (event.target.id.match(/payment_.*_hipay_paypalapi-head/) ||
            event.target.id.match(/payment_.*_hipay_hosted_paypal-head/))
    ) {
      setTimeout(debouncedHandler, 0);
    }
  });

  const select = document.querySelector(
      '[id^="payment_"][id*="_hipay_hosted_payment_products"]'
  );
  if (select) {
    select.addEventListener('change', checkPayPalSelected);
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
});