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

  // number of mandatory agreements declared in config
  function requiredCount() {
    var agreements = window.checkoutConfig.checkoutAgreements.agreements || [];
    return agreements.filter(function (agreement) {
      return agreement.mode == '1';
    }).length;
  }

  // hipay per-method checkbox first, fallback to the standard agreements
  function resolveBoxes(entry) {
    var boxes = document.querySelectorAll(entry.specific);

    if (boxes.length === 0 && entry.fallback) {
      boxes = document.querySelectorAll(entry.fallback);
    }

    return boxes;
  }

  // compute the checked state for a single registered renderer
  function computeEntry(entry, required) {
    var boxes = resolveBoxes(entry);
    var allChecked =
      boxes.length > 0 &&
      boxes.length === required &&
      Array.prototype.every.call(boxes, function (box) {
        return box.checked;
      });

    entry.observable(allChecked);
  }

  // recompute every registered renderer (on a real change event)
  function recompute() {
    var required = requiredCount();

    entries.forEach(function (entry) {
      computeEntry(entry, required);
    });
  }

  return {
    // register a renderer: track its agreements and update its isAllTOCChecked
    register: function (specificSelector, fallbackSelector, observable) {
      if (!window.checkoutConfig.checkoutAgreements.isEnabled) {
        return;
      }

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
        // component re-initialized: point to the live observable
        entry.fallback = fallbackSelector || null;
        entry.observable = observable;
      }

      // single delegated listener, bound once for all renderers
      if (!bound) {
        bound = true;
        $(document).on(
          'change',
          '.checkout-agreements input[type="checkbox"][id*="agreement"], input[id*="agreement_hipay_"]',
          recompute
        );
      }

      // only compute the entry we just registered, not all of them
      computeEntry(entry, requiredCount());
    }
  };
});
