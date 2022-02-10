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
 */

var currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test Magento Without Device Fingerprint', function (test) {
  phantom.clearCookies();

  var ioBB = '';
  casper
    .start(baseURL)
    .then(function () {
      if (this.visible('p[class="bugs"]')) {
        test.done();
      }
    })
    .thenOpen(baseURL + 'admin/', function () {
      adminMod.logToBackend(baseURL, admin_login, admin_passwd);
    })
    /* Active device fingerprint */
    .then(function () {
      adminMod.setDeviceFingerprint(test, '0', configuration);
    })
    .thenOpen(baseURL, function () {
      checkoutMod.selectItemAndOptions(test);
    })
    .then(function () {
      checkoutMod.addItemGoCheckout(test);
    })
    .then(function () {
      checkoutMod.billingInformation(test, 'FR');
    })
    .then(function () {
      checkoutMod.shippingMethod(test);
    })
    /* Check no ioBB field */
    .then(function () {
      this.waitForSelector(
        '#hipay_cc',
        function success() {
          this.echo(
            "Checking 'ioBB' field NOT inside checkout page...",
            'INFO'
          );
          test.assertDoesntExist('input#ioBB', "'ioBB' field is Not present !");
        },
        function fail() {
          test.assertVisible(
            '#checkout-step-payment',
            "'Payment Information' formular exists"
          );
        },
        10000
      );
    })
    .run(function () {
      test.done();
    });
});
