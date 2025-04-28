define([
  'jquery',
  'Magento_Customer/js/customer-data'
], function ($, customerData) {
  'use strict';

  return function () {

    var sections = ['cart'];
    customerData.invalidate(sections);
    customerData.reload(sections, true);

    var checkInterval = setInterval(function() {
      if ($('[data-role=reload-minicart]').length) {
        customerData.reload(['cart'], true);
        clearInterval(checkInterval);
      }
    }, 1000);

    setTimeout(function() {
      clearInterval(checkInterval);
    }, 5000);
  };
});