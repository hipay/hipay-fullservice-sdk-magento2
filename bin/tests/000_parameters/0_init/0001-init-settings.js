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

/**********************************************************************************************
 *                       Init settings
 /**********************************************************************************************/
casper.test.begin('Init settings', function (test) {
  phantom.clearCookies();

  casper
    .start(baseURL)
    .thenOpen(urlBackend, function () {
      backendLibHiPay.logToHipayBackend(test, loginBackend, passBackend);
    })
    .then(function () {
      backendLibHiPay.selectAccountBackend(test, 'OGONE_DEV');
    })
    /* Open Integration tab */
    .then(function () {
      this.echo('Open Integration nav', 'INFO');
      this.waitForUrl(
        /maccount/,
        function success() {
          backendLibHiPay.selectHashingAlgorithm(test, 'SHA1');
        },
        function fail() {
          test.assertUrlMatch(
            /maccount/,
            'Dashboard page with account ID exists'
          );
        }
      );
    })
    .run(function () {
      test.done();
    });
});
