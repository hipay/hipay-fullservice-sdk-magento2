define(['hipayAvailablePaymentProducts', 'jquery', 'domReady!'], function (
    availablePaymentProducts,
    $
) {
  'use strict';

  const DEFAULT_MIN_ORDER = 50;
  const DEFAULT_MAX_ORDER = 2000;
  const CURRENCY = 'EUR';
  const ALMA_3X = 'alma-3x';
  const ALMA_4X = 'alma-4x';

  // Using dynamic selectors to handle different region prefixes beyond 'us'
  function getSelector(pattern) {
    return `[id^="payment_"][id*="${pattern}"]`;
  }

  function getRowSelector(pattern) {
    return `[id^="row_payment_"][id*="${pattern}"]`;
  }

  const PAYMENT_FIELD_IDS = {
    ALMA_3X: {
      MIN: getSelector('_hipay_alma3X_min_order_total'),
      MAX: getSelector('_hipay_alma3X_max_order_total'),
      HOSTED_MIN: getSelector('_hipay_hosted_alma_3x_min_order_total'),
      HOSTED_MAX: getSelector('_hipay_hosted_alma_3x_max_order_total'),
      HEAD: getSelector('_hipay_alma3X-head'),
      SECTION: getSelector('_hipay_alma3X'),
      ROW: getRowSelector('_hipay_hosted_alma_3x')
    },
    ALMA_4X: {
      MIN: getSelector('_hipay_alma4X_min_order_total'),
      MAX: getSelector('_hipay_alma4X_max_order_total'),
      HOSTED_MIN: getSelector('_hipay_hosted_alma_4x_min_order_total'),
      HOSTED_MAX: getSelector('_hipay_hosted_alma_4x_max_order_total'),
      HEAD: getSelector('_hipay_alma4X-head'),
      SECTION: getSelector('_hipay_alma4X'),
      ROW: getRowSelector('_hipay_hosted_alma_4x')
    },
    HOSTED_PRODUCTS: getSelector('_hipay_hosted_payment_products')
  };

  let isAlmaInitialized = false;
  let initAlmaPromise = null;
  let paymentProductsInstance = null;
  let isAlma3xSelected = false;
  let isAlma4xSelected = false;

  // Set default values
  Object.values(PAYMENT_FIELD_IDS.ALMA_3X)
      .filter((selector) => selector.includes('min') || selector.includes('max'))
      .forEach((selector) =>
          createOrUpdateValueDisplay(
              selector,
              selector.includes('min') ? DEFAULT_MIN_ORDER : DEFAULT_MAX_ORDER
          )
      );

  Object.values(PAYMENT_FIELD_IDS.ALMA_4X)
      .filter((selector) => selector.includes('min') || selector.includes('max'))
      .forEach((selector) =>
          createOrUpdateValueDisplay(
              selector,
              selector.includes('min') ? DEFAULT_MIN_ORDER : DEFAULT_MAX_ORDER
          )
      );

  function initializePaymentProducts() {
    try {
      if (!paymentProductsInstance && typeof hipayConfig !== 'undefined') {
        // Check if all required properties exist on hipayConfig
        if (!hipayConfig.getApiUsernameTokenJs ||
            !hipayConfig.getApiPasswordTokenJs ||
            typeof hipayConfig.getEnv === 'undefined') {
          console.error('HiPay configuration is incomplete');
          return null;
        }

        paymentProductsInstance = availablePaymentProducts();
        if (paymentProductsInstance) {
          paymentProductsInstance.setCredentials(
              hipayConfig.getApiUsernameTokenJs,
              hipayConfig.getApiPasswordTokenJs,
              hipayConfig.getEnv === 'stage'
          );
        }
      }
      return paymentProductsInstance;
    } catch (error) {
      console.error('Error initializing payment products:', error);
      return null;
    }
  }

  function createOrUpdateValueDisplay(fieldSelector, value) {
    const field = $(fieldSelector);
    if (field.length === 0) return;

    const parentCell = field.closest('.value');
    let wrapper = parentCell.find('.alma-amount-wrapper');

    wrapper.find('.alma-loader').remove();
    let displaySpan = wrapper.find('.alma-amount-display');

    if (wrapper.length === 0) {
      wrapper = $('<div>').addClass('alma-amount-wrapper');
      displaySpan = $('<span>').addClass('alma-amount-display');
      wrapper.append(displaySpan);
      parentCell.prepend(wrapper);
    }

    displaySpan.text(`${value} ${CURRENCY}`);
    field.val(value);
  }

  function addLoaders() {
    const selectors = [
      ...Object.values(PAYMENT_FIELD_IDS.ALMA_3X).filter((selector) =>
          selector.includes('total')
      ),
      ...Object.values(PAYMENT_FIELD_IDS.ALMA_4X).filter((selector) =>
          selector.includes('total')
      )
    ];

    selectors.forEach((fieldSelector) => {
      const field = $(fieldSelector);
      if (field.length === 0) return;

      const parentCell = field.closest('.value');
      let wrapper = parentCell.find('.alma-amount-wrapper');

      if (wrapper.length === 0) {
        wrapper = $('<div>').addClass('alma-amount-wrapper');
        wrapper.append($('<div>').addClass('alma-loader'));
        wrapper.append($('<span>').addClass('alma-amount-display'));
        parentCell.prepend(wrapper);
        $('.alma-amount-display').hide();
      } else {
        wrapper.find('.alma-amount-display');
        if (!wrapper.find('.alma-loader').length) {
          wrapper.prepend($('<div>').addClass('alma-loader'));
          $('.alma-amount-display').hide();
        }
      }
    });
  }

  function updateAlmaAmountFields(productData) {
    productData.forEach((product) => {
      if (product.code === ALMA_3X) {
        const minAmount = product.options?.basketAmountMin3x;
        const maxAmount = product.options?.basketAmountMax3x;

        if (minAmount) {
          createOrUpdateValueDisplay(PAYMENT_FIELD_IDS.ALMA_3X.MIN, minAmount);
          createOrUpdateValueDisplay(
              PAYMENT_FIELD_IDS.ALMA_3X.HOSTED_MIN,
              minAmount
          );
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(PAYMENT_FIELD_IDS.ALMA_3X.MAX, maxAmount);
          createOrUpdateValueDisplay(
              PAYMENT_FIELD_IDS.ALMA_3X.HOSTED_MAX,
              maxAmount
          );
        }
      } else if (product.code === ALMA_4X) {
        const minAmount = product.options?.basketAmountMin4x;
        const maxAmount = product.options?.basketAmountMax4x;

        if (minAmount) {
          createOrUpdateValueDisplay(PAYMENT_FIELD_IDS.ALMA_4X.MIN, minAmount);
          createOrUpdateValueDisplay(
              PAYMENT_FIELD_IDS.ALMA_4X.HOSTED_MIN,
              minAmount
          );
        }
        if (maxAmount) {
          createOrUpdateValueDisplay(PAYMENT_FIELD_IDS.ALMA_4X.MAX, maxAmount);
          createOrUpdateValueDisplay(
              PAYMENT_FIELD_IDS.ALMA_4X.HOSTED_MAX,
              maxAmount
          );
        }
      }
    });
  }

  function checkAlmaSelected() {
    const select = document.querySelector(PAYMENT_FIELD_IDS.HOSTED_PRODUCTS);
    if (select) {
      const selectedOptions = Array.from(select.selectedOptions);
      isAlma3xSelected = selectedOptions.some(
          (option) => option.value === ALMA_3X
      );
      isAlma4xSelected = selectedOptions.some(
          (option) => option.value === ALMA_4X
      );

      const alma3xRow = document.querySelector(PAYMENT_FIELD_IDS.ALMA_3X.ROW);
      const alma4xRow = document.querySelector(PAYMENT_FIELD_IDS.ALMA_4X.ROW);

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

    // Check if instance is null or undefined before using it
    if (!instance) {
      console.error('Payment products instance is null or undefined');
      $('.alma-loader').remove();
      $('.alma-amount-display').text('Configuration error');
      initAlmaPromise = Promise.reject(new Error('Payment products instance is null'));
      return initAlmaPromise;
    }

    instance.updateConfig('payment_product', [ALMA_3X, ALMA_4X]);
    instance.updateConfig('with_options', true);
    instance.updateConfig('currency', [CURRENCY]);

    initAlmaPromise = instance
        .getAvailableProducts()
        .then((result) => {
          $('.alma-loader').remove();
          $('.alma-amount-display').show();
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
    // Using jQuery's :visible selector with our dynamic selectors
    const alma3XActive = $(PAYMENT_FIELD_IDS.ALMA_3X.SECTION).is(':visible');
    const alma4XActive = $(PAYMENT_FIELD_IDS.ALMA_4X.SECTION).is(':visible');
    const hostedAlma3XActive = $(PAYMENT_FIELD_IDS.ALMA_3X.ROW).is(':visible');
    const hostedAlma4XActive = $(PAYMENT_FIELD_IDS.ALMA_4X.ROW).is(':visible');

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

  const debouncedHandler = (function () {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(handleHiPayAlmaSection, 250);
    };
  })();

  handleHiPayAlmaSection();

  // Using event delegation with dynamic selectors
  $(document).on(
      'click',
      `${PAYMENT_FIELD_IDS.ALMA_3X.HEAD}, ${PAYMENT_FIELD_IDS.ALMA_4X.HEAD}`,
      function () {
        isAlmaInitialized = false;
        debouncedHandler();
      }
  );

  const select = document.querySelector(PAYMENT_FIELD_IDS.HOSTED_PRODUCTS);
  if (select) {
    select.addEventListener('change', checkAlmaSelected);
  }

  new MutationObserver(debouncedHandler).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class', 'style']
  });
});