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

casper.test.begin(
    'Test Magento Admin Menus', function (test) {
        phantom.clearCookies();

        casper.start(baseURL + "admin/")
        .then(
            function () {
                adminMod.logToBackend(baseURL,admin_login,admin_passwd);
            }
        )
        /* Check HiPay Split Payments menu */
        .then(
            function () {
                this.echo("Checking HiPay Split Payments menu...", "INFO");
                this.waitForUrl(
                    /admin\/dashboard/, function success()
                    {

                    this.wait(
                        500, function () {
                            this.click('#menu-hipay-fullservicemagento-hipay-payment-menu a span');

                            this.waitUntilVisible(
                                '.item-hipay-split-payment.level-1 a span', function () {
                                    this.click('.item-hipay-split-payment.level-1 a span');
                                }, function fail()
                                {
                                test.assertExists(this.visible('.item-hipay-split-payment.level-1 a span'), "Configuration menu exists");
                                }, 30000
                            );

                            this.waitForUrl(
                                /admin\/hipay\/splitpayment/, function success()
                                {
                                    test.assertTextExists('Split Payments', "HiPay Split Payments menu activated !");
                                }, function fail()
                                {
                                    test.assertUrlMatch(/admin\/hipay\/splitpayment/, "Split Payments admin page exists");
                                }, 10000
                            );
                        }, function fail()
                        {
                            test.assertUrlMatch(/admin\/dashboard/, "Dashboard admin page exists");
                        }, 10000
                    );
                    }
                );
            }
        )
        /* Check HiPay Enterprise menu */
        .then(
            function () {
                this.echo("Checking Hipay Enterprise menu...", "INFO");

                configuration.goingToHiPayConfiguration(test);
            }
        )
        /* Check Payment Methods bloc count */
        .then(
            function () {
                this.echo("Checking Payments Methods blocs...", "INFO");
                configuration.goingToHiPayMethodsConfiguration(test);
                this.waitForSelector(
                    '#payment_us_other_payment_methods-head', function success()
                    {
                        test.assert(this.exists('#payment_us_hipay_cc-head'), "Payments Methods blocs exists !");
                    }, function fail()
                    {
                        test.assertExists('#payment_us_other_payment_methods-head', "Payment Methods admin page exists");
                    }, 10000
                )
            }
        )
        .run(
            function () {
                test.done();
            }
        );
    }
);
