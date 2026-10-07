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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
define(['jquery'], function ($) {
  'use strict';

  var entries = [];
  var bound = false;

  function agreementsConfig() {
    return (window.checkoutConfig && window.checkoutConfig.checkoutAgreements) || {};
  }

  function requiredCount() {
    var config = agreementsConfig();

    if (!config.isEnabled) {
      return 0;
    }

    return (config.agreements || []).filter(function (agreement) {
      return agreement.mode == '1';
    }).length;
  }

  // HiPay per-method checkbox first, fallback to the standard agreements
  function resolveBoxes(entry) {
    var boxes = Array.prototype.slice.call(document.querySelectorAll(entry.specific));

    if (boxes.length === 0 && entry.fallback) {
      boxes = Array.prototype.slice.call(document.querySelectorAll(entry.fallback))
        .filter(function (box) {
          return !box.closest('.payment-method');
        });
    }

    return boxes;
  }

  function isVisible(box) {
    return $(box.closest('.checkout-agreement') || box.parentElement).is(':visible');
  }

  function computeEntry(entry, required) {
    var boxes = resolveBoxes(entry);

    // Duplicated agreements block: only visible boxes count
    if (boxes.length > required) {
      boxes = boxes.filter(isVisible);
    }

    var allChecked =
      boxes.length > 0 &&
      boxes.length >= required &&
      boxes.every(function (box) {
        return box.checked;
      });

    entry.observable(allChecked);
  }

  function recompute() {
    var required = requiredCount();

    entries.forEach(function (entry) {
      computeEntry(entry, required);
    });
  }

  return {
    hasMandatoryAgreements: function () {
      return requiredCount() > 0;
    },

    register: function (specificSelector, fallbackSelector, observable) {
      if (requiredCount() === 0) {
        return;
      }

      var entry = entries.filter(function (item) {
        return item.specific === specificSelector;
      })[0];

      if (!entry) {
        entry = {
          specific: specificSelector,
          fallback: fallbackSelector || null,
          observable: observable
        };
        entries.push(entry);
      } else {
        // Component re-initialized: use the live observable
        entry.fallback = fallbackSelector || null;
        entry.observable = observable;
      }

      // Single delegated listener for all renderers
      if (!bound) {
        bound = true;
        $(document).on(
          'change',
          '.checkout-agreements input[type="checkbox"][id*="agreement"], input[id*="agreement_hipay_"]',
          recompute
        );
      }

      computeEntry(entry, requiredCount());
    }
  };
});
