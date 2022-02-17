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

var paymentType = 'HiPay Enterprise Credit Card',
  currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin(
  'Change Hash Algorithm ' + paymentType + ' with ' + currentBrandCC,
  function (test) {
    phantom.clearCookies();

    casper.setFilter('page.confirm', function (msg) {
      this.echo('Confirmation message ' + msg, 'INFO');
      return true;
    });

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
      .thenOpen(baseURL + 'admin/', function () {
        adminMod.logToBackend(baseURL, admin_login, admin_passwd);
      })
      .then(function () {
        configuration.goingToHiPayConfiguration(test);
      })
      .then(function () {
        var open = this.getElementAttribute(
          '#hipay_hashing_algorithm-head',
          'class'
        );
        if (open !== 'open') {
          test.info('Collapse bloc is closed. Try to expand it.');
          this.wait(500, function () {
            this.click('#hipay_hashing_algorithm-head');
          });
        }
      })
      .then(function () {
        this.echo('Synchronize Hashing Algorithm', 'INFO');
        this.waitUntilVisible(
          'button#hashing_algorithm_button',
          function success() {
            var current = this.evaluate(function () {
              return document.querySelector(
                '#hipay_hashing_algorithm_hashing_algorithm_test'
              ).value;
            });
            test.info('Initial Hashing Algorithm :' + current);
            if (current != 'SHA512') {
              test.fail('Initial value is wrong for Hashing : ' + current);
            }
            this.thenClick('button#hashing_algorithm_button', function () {
              this.waitForSelector(
                'aside.modal-popup.confirm._show',
                function () {
                  this.click(
                    'aside.modal-popup.confirm._show button.action-accept'
                  );
                }
              );
            });

            this.then(function () {
              this.waitForSelector(
                'button#hashing_algorithm_button',
                function success() {
                  var newHashingAlgo = this.evaluate(function () {
                    return document.querySelector(
                      '#hipay_hashing_algorithm_hashing_algorithm_test'
                    ).value;
                  });
                  if (newHashingAlgo != 'SHA1') {
                    test.fail("Synchronize doesn't work : " + current);
                  } else {
                    test.info('Done');
                  }
                }
              );
            });
          },
          function fail() {
            test.assertExists(
              'button#hashing_algorithm_button',
              'Syncronize button exist'
            );
          }
        );
      })
      .thenOpen(
        baseURL,
        function () {
          this.waitUntilVisible(
            'div.footer',
            function success() {
              checkoutMod.selectItemAndOptions(test);
            },
            function fail() {
              test.assertVisible('div.footer', "'Footer' exists");
            },
            10000
          );
        },
        15000
      )
      .then(function () {
        checkoutMod.addItemGoCheckout(test);
      })
      .then(function () {
        checkoutMod.billingInformation(test, 'FR');
      })
      .then(function () {
        checkoutMod.shippingMethod(test);
      })
      /* Fill steps payment */
      .then(function () {
        checkoutMod.fillStepPayment(
          test,
          false,
          'hipay_cc',
          currentBrandCC,
          parametersLibHiPay
        );
      })
      .then(function () {
        adminMod.orderResult(test, paymentType, order);
      })
      .thenOpen(urlBackend, function () {
        notificationLibHiPay.processNotifications(
          test,
          order.getId(),
          true,
          false,
          true,
          false,
          'OGONE_DEV',
          backendLibHiPay,
          loginBackend,
          passBackend,
          baseURL,
          urlNotification,
          urlBackend
        );
      })
      .run(function () {
        test.done();
      });
  }
);
