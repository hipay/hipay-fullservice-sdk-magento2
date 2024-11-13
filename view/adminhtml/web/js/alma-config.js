define(['hipayAvailablePaymentProducts', 'jquery', 'domReady!'], function (
  availablePaymentProducts,
  $
) {
  'use strict';
  let isAlmaInitialized = false;
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
      'payment_us_hipay_alma4X_max_order_total'
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
    console.log('Updating Alma amount fields with data:', productData);

    productData.forEach((product) => {
      if (product.code === 'alma-3x') {
        const minAmount = product.options?.basketAmountMin3x;
        const maxAmount = product.options?.basketAmountMax3x;

        if (minAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma3X_min_order_total',
            minAmount
          );
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma3X_max_order_total',
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
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(
            'payment_us_hipay_alma4X_max_order_total',
            maxAmount
          );
        }
      }
    });
  }

  function checkAlmaConfiguration() {
    if (isAlmaInitialized) {
      return Promise.resolve();
    }

    addLoaders();
    const instance = initializePaymentProducts();

    // Configure the payment products request for Alma
    instance.updateConfig('payment_product', ['alma-3x', 'alma-4x']);
    instance.updateConfig('with_options', true);
    instance.updateConfig('currency', ['EUR']);

    return instance
      .getAvailableProducts()
      .then((result) => {
        console.log('Received Alma products:', result);
        isAlmaInitialized = true;
        updateAlmaAmountFields(result);
      })
      .catch((error) => {
        console.error('Error fetching Alma configuration:', error);
        // Remove loaders and show error state
        $('.alma-loader').remove();
        $('.alma-amount-display').text('Error loading values');
      });
  }

  function handleHiPayAlmaSection() {
    const alma3XActive = $('#payment_us_hipay_alma3X').is(':visible');
    const alma4XActive = $('#payment_us_hipay_alma4X').is(':visible');

    console.log('Section status:', { alma3XActive, alma4XActive });

    if (alma3XActive || alma4XActive) {
      checkAlmaConfiguration();
    }
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
      console.log('Alma section header clicked:', e.target.id);
      isAlmaInitialized = false; // Reset initialization to show loader again
      debouncedHandler();
    }
  );

  // Observe section expansions/collapses
  new MutationObserver(debouncedHandler).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class', 'style']
  });
});
