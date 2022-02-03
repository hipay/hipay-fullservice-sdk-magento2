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

casper.test.begin(
    'Test Payment With Incorrect Credentials', function (test) {
        phantom.clearCookies();
        var paymentType = "HiPay Enterprise Credit Card",
        initialCredential;

        casper.start(baseURL + "admin/")
        .then(
            function () {
                this.clearCache();
            }
        )
        .then(
            function () {
                adminMod.logToBackend(baseURL,admin_login,admin_passwd);
                method.configure(test, paymentType, "cc", "", configuration);
            }
        )
        /* Disactive MOTO option */
        .then(
            function () {
                configuration.goingToHiPayConfiguration(test);
            }
        )
        /* Set bad credentials inside HiPay Entreprise formular */
        .then(
            function () {
                initialCredential = this.evaluate(
                    function () {
                        return document.querySelector('input[name="groups[hipay_credentials][fields][api_username_test][value]"]').value;
                    }
                );

                test.info("Initial credential for api_user_name was :" + initialCredential);
                adminMod.fillFormHipayEnterprise(test, "blabla");
            }
        )
        .thenOpen(
            baseURL, function () {
                checkoutMod.selectItemAndOptions(test);
            }
        )
        .then(
            function () {
                checkoutMod.addItemGoCheckout(test);
            }
        )
        .then(
            function () {
                checkoutMod.billingInformation(test, "FR");
            }
        )
        .then(
            function () {
                checkoutMod.shippingMethod(test);
            }
        )
        /* HiPay CC payment */
        .then(
            function () {
                checkoutMod.fillStepPayment(test, false, "hipay_cc", currentBrandCC, parametersLibHiPay);
            }
        )
        /* Check failure page */
        .then(
            function () {
                this.echo("Checking order failure cause of incorrect credentials...", "INFO");
                this.waitForSelector(
                    '.message.message-error.error', function success()
                    {
                        test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                        test.assertSelectorHasText(
                            '.message.message-error.error',
                            "\n        There was an error request new transaction: Incorrect Credentials : API User Not Found\n.\n    ",
                            "Correct response from Magento server !"
                        );
                    }, function fail()
                    {
                        test.assertExists('.message.message-error.error', "Correct response from Magento server !");
                    }, 15000
                );
            }
        )
        .then(
            function () {
                adminMod.logToBackend(baseURL,admin_login,admin_passwd);
            }
        )
        .then(
            function () {
                this.echo("Accessing to Hipay Enterprise menu...", "INFO");
                configuration.goingToHiPayConfiguration(test);
            }
        )
        /* Reinitialize credentials inside HiPay Enterprise */
        .then(
            function () {
                test.info("Initial credential for api_user_name was :" + initialCredential);
                adminMod.fillFormHipayEnterprise(test, initialCredential);
            }
        )
        .run(
            function () {
                test.done();
            }
        );
    }
);
