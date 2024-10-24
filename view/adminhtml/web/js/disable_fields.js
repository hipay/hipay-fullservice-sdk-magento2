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
  'HiPay_FullserviceMagento/js/hipay-paypal-config',
  'domReady!'
], function (hipayPaypalConfig) {
  'use strict';

  let isV2 = false;
  let isPayPalSelected = false;
  let initPayPalPromise = null;

  function checkPayPalV2() {
    if (initPayPalPromise !== null && isV2 !== false) {
      return initPayPalPromise;
    }

    initPayPalPromise = new Promise((resolve, reject) => {
      if (
        typeof hipayPaypalConfig.createHipayAvailablePaymentProducts ===
          'function' &&
        typeof hipayConfig !== 'undefined' &&
        hipayConfig.getApiUsernameTokenJs &&
        hipayConfig.getApiPasswordTokenJs &&
        typeof hipayConfig.getEnv !== 'undefined'
      ) {
        const config = hipayPaypalConfig.createHipayAvailablePaymentProducts(
          hipayConfig.getApiUsernameTokenJs,
          hipayConfig.getApiPasswordTokenJs,
          hipayConfig.getEnv === 'stage'
        );

        if (typeof config?.getAvailablePaymentProducts === 'function') {
          config
            .getAvailablePaymentProducts('paypal', '7', '4', 'true')
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
      const hostedField = document.getElementById(
        `payment_us_hipay_paypalapi_${fieldId}`
      );
      const hostedPageField = document.getElementById(
        `payment_us_hipay_hosted_paypal_${fieldId}`
      );
      if (hostedField) hostedField.disabled = !shouldEnable;
      if (hostedPageField)
        hostedPageField.disabled = !shouldEnable || !isPayPalSelected;
    });

    const v2StatusRow = document.querySelector(
      '#row_payment_us_hipay_paypalapi_paypal_v2_status'
    );
    const v2StatusRowHostedPage = document.querySelector(
      '#row_payment_us_hipay_hosted_paypal_v2_status'
    );
    if (v2StatusRow) {
      v2StatusRow.style.display =
        shouldEnable && isPayPalSelected ? 'none' : 'table-row';
    }
    if (v2StatusRowHostedPage) {
      v2StatusRowHostedPage.style.display =
        shouldEnable && isPayPalSelected ? 'none' : 'table-row';
    }

    const paypalHostedRow = document.getElementById(
      'row_payment_us_hipay_hosted_paypal'
    );
    if (paypalHostedRow) {
      paypalHostedRow.style.display = isPayPalSelected ? '' : 'none';
    }
  }

  function checkPayPalSelected() {
    const select = document.getElementById(
      'payment_us_hipay_hosted_payment_products'
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
        .querySelector('#row_payment_us_hipay_paypalapi .section-config')
        ?.classList.contains('active') || false;
    const isActiveHostedPage =
      document
        .querySelector('#row_payment_us_hipay_hosted_paypal .section-config')
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
      event?.target.id === 'payment_us_hipay_paypalapi-head' ||
      event?.target.id === 'payment_us_hipay_hosted_paypal-head'
    ) {
      setTimeout(debouncedHandler, 0);
    }
  });

  const select = document.getElementById(
    'payment_us_hipay_hosted_payment_products'
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
