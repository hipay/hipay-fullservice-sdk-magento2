define(['hipayAvailablePaymentProducts', 'jquery', 'domReady!'], function (
  availablePaymentProducts,
  $
) {
  'use strict';
  let isAlmaInitialized = false;
  let initAlmaPromise = null;
  let paymentProductsInstance = null;
  let isAlma3xSelected = false;
  let isAlma4xSelected = false;

  // Set default values for Alma 3X and 4X
  createOrUpdateValueDisplay('payment_us_hipay_alma3X_min_order_total', 50);
  createOrUpdateValueDisplay('payment_us_hipay_alma3X_max_order_total', 2000);
  createOrUpdateValueDisplay('payment_us_hipay_alma4X_min_order_total', 50);
  createOrUpdateValueDisplay('payment_us_hipay_alma4X_max_order_total', 2000);
  createOrUpdateValueDisplay(
    'payment_us_hipay_hosted_alma_3x_min_order_total',
    50
  );
  createOrUpdateValueDisplay(
    'payment_us_hipay_hosted_alma_3x_max_order_total',
    2000
  );
  createOrUpdateValueDisplay(
    'payment_us_hipay_hosted_alma_4x_min_order_total',
    50
  );
  createOrUpdateValueDisplay(
    'payment_us_hipay_hosted_alma_4x_max_order_total',
    2000
  );

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

  function createOrUpdateValueDisplay(fieldId, value) {
    const field = $(`#${fieldId}`);
    const parentCell = field.closest('.value');
    let wrapper = parentCell.find('.alma-amount-wrapper');

    // Remove existing loader if any
    wrapper.find('.alma-loader').remove();

    let displaySpan = wrapper.find('.alma-amount-display');

    if (wrapper.length === 0) {
      wrapper = $('<div>').addClass('alma-amount-wrapper');
      displaySpan = $('<span>').addClass('alma-amount-display');
      wrapper.append(displaySpan);
      parentCell.prepend(wrapper);
    }

    displaySpan.text(`${value} €`);
    field.val(value);
  }

  function addLoaders() {
    const fields = [
      'payment_us_hipay_alma3X_min_order_total',
      'payment_us_hipay_alma3X_max_order_total',
      'payment_us_hipay_alma4X_min_order_total',
      'payment_us_hipay_alma4X_max_order_total',
      'payment_us_hipay_hosted_alma_3x_min_order_total',
      'payment_us_hipay_hosted_alma_3x_max_order_total',
      'payment_us_hipay_hosted_alma_4x_min_order_total',
      'payment_us_hipay_hosted_alma_4x_max_order_total'
    ];

    fields.forEach((fieldId) => {
      const field = $(`#${fieldId}`);
      const parentCell = field.closest('.value');
      let wrapper = parentCell.find('.alma-amount-wrapper');

      if (wrapper.length === 0) {
        wrapper = $('<div>').addClass('alma-amount-wrapper');
        wrapper.append($('<div>').addClass('alma-loader'));
        wrapper.append($('<span>').addClass('alma-amount-display'));
        parentCell.prepend(wrapper);
      } else {
        wrapper.find('.alma-amount-display');
        if (!wrapper.find('.alma-loader').length) {
          wrapper.prepend($('<div>').addClass('alma-loader'));
        }
      }
    });
  }

  function updateAlmaAmountFields(productData) {
    productData.forEach((product) => {
      if (product.code === 'alma-3x') {
        const minAmount = product.options?.basketAmountMin3x;
        const maxAmount = product.options?.basketAmountMax3x;

        if (minAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma3X_min_order_total',
            minAmount
          );
          createOrUpdateValueDisplay(
            'payment_us_hipay_hosted_alma_3x_min_order_total',
            minAmount
          );
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma3X_max_order_total',
            maxAmount
          );
          createOrUpdateValueDisplay(
            'payment_us_hipay_hosted_alma_3x_max_order_total',
            maxAmount
          );
        }
      } else if (product.code === 'alma-4x') {
        const minAmount = product.options?.basketAmountMin4x;
        const maxAmount = product.options?.basketAmountMax4x;

        if (minAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma4X_min_order_total',
            minAmount
          );
          createOrUpdateValueDisplay(
            'payment_us_hipay_hosted_alma_4x_min_order_total',
            minAmount
          );
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma4X_max_order_total',
            maxAmount
          );
          createOrUpdateValueDisplay(
            'payment_us_hipay_hosted_alma_4x_max_order_total',
            maxAmount
          );
        }
      }
    });
  }

  function checkAlmaSelected() {
    const select = document.getElementById(
      'payment_us_hipay_hosted_payment_products'
    );
    if (select) {
      const selectedOptions = Array.from(select.selectedOptions);
      isAlma3xSelected = selectedOptions.some(
        (option) => option.value === 'alma-3x'
      );
      isAlma4xSelected = selectedOptions.some(
        (option) => option.value === 'alma-4x'
      );

      // Toggle visibility of hosted Alma rows
      const alma3xRow = document.getElementById(
        'row_payment_us_hipay_hosted_alma_3x'
      );
      const alma4xRow = document.getElementById(
        'row_payment_us_hipay_hosted_alma_4x'
      );

      if (alma3xRow) {
        alma3xRow.style.display = isAlma3xSelected ? '' : 'none';
      }
      if (alma4xRow) {
        alma4xRow.style.display = isAlma4xSelected ? '' : 'none';
      }
    }
  }

  function checkAlmaConfiguration() {
    if (initAlmaPromise !== null) {
      return initAlmaPromise;
    }

    addLoaders();
    const instance = initializePaymentProducts();

    instance.updateConfig('payment_product', ['alma-3x', 'alma-4x']);
    instance.updateConfig('with_options', true);
    instance.updateConfig('currency', ['EUR']);

    initAlmaPromise = instance
      .getAvailableProducts()
      .then((result) => {
        updateAlmaAmountFields(result);
        return result;
      })
      .catch((error) => {
        console.error('Error fetching Alma configuration:', error);
        $('.alma-loader').remove();
        $('.alma-amount-display').text('Error loading values');
        throw error;
      });

    return initAlmaPromise;
  }

  function handleHiPayAlmaSection() {
    const alma3XActive = $('#payment_us_hipay_alma3X').is(':visible');
    const alma4XActive = $('#payment_us_hipay_alma4X').is(':visible');
    const hostedAlma3XActive = $('#row_payment_us_hipay_hosted_alma_3x').is(
      ':visible'
    );
    const hostedAlma4XActive = $('#row_payment_us_hipay_hosted_alma_4x').is(
      ':visible'
    );

    if (
      alma3XActive ||
      alma4XActive ||
      hostedAlma3XActive ||
      hostedAlma4XActive
    ) {
      checkAlmaConfiguration();
    }

    checkAlmaSelected();
  }

  // Create a debounced version of the handler
  const debouncedHandler = (function () {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(handleHiPayAlmaSection, 250);
    };
  })();

  // Initial setup
  handleHiPayAlmaSection();

  // Setup observers and event listeners
  $(document).on(
    'click',
    '#payment_us_hipay_alma3X-head, #payment_us_hipay_alma4X-head',
    function (e) {
      isAlmaInitialized = false;
      debouncedHandler();
    }
  );

  // Add event listener for payment products select
  const select = document.getElementById(
    'payment_us_hipay_hosted_payment_products'
  );
  if (select) {
    select.addEventListener('change', checkAlmaSelected);
  }

  // Observe section expansions/collapses
  new MutationObserver(debouncedHandler).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class', 'style']
  });
});
