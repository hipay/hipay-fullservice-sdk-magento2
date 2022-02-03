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

var casper;
var x = require('casper').selectXPath;

/**
 * Log to Magento2 Backend
 *
 * @param test
 */
exports.logToBackend = function logToBackend( baseURL, admin_login, admin_passwd)
{
    casper.thenOpen(
        baseURL + "/admin", function () {
            this.echo("Connecting to admin panel...", "INFO");
            this.waitForSelector(
                "#login-form", function success()
                {
                    this.fillSelectors(
                        'form#login-form', {
                            'input[name="login[username]"]': admin_login,
                            'input[name="login[password]"]': admin_passwd
                        }, false
                    );
                    this.click('.action-login');
                    this.waitForSelector(
                        "#menu-magento-backend-dashboard", function success()
                        {
                            this.echo("Connected", "INFO");
                        }, function fail()
                        {
                            test.assertExists(".message-error", "Incorrect credentials !");
                        }, 20000
                    );
                }, function fail()
                {
                    this.waitForUrl(
                        /admin\/dashboard/, function success()
                        {
                            this.echo("Already logged to admin panel !", "INFO");
                        }, function fail()
                        {
                            test.assertUrlMatch(/admin\/dashboard/, "Admin dashboard exists");
                        }
                    );
                }
            );
        }
    );
};

/**
 * Configure HiPay Enterprise options via formular
 *
 * @param test
 * @param credentials
 * @param moto
 */
exports.fillFormHipayEnterprise = function fillFormHipayEnterprise(test, credentials, moto)
{
    casper.then(
        function () {
            var stringMoto = "";
            if (moto) {
                stringMoto = " MOTO";
            }

            if (credentials == "blabla") {
                this.echo("Editing Credentials" + stringMoto + " configuration with bad credentials...", "INFO");
            } else {
                this.echo("Reinitializing Credentials" + stringMoto + " configuration...", "INFO");
            }

            if (moto) {
                this.fillSelectors(
                    "form#config-edit-form",
                    {'input[name="groups[hipay_credentials_moto][fields][api_username_test][value]"]': credentials}
                    , false
                );
            } else {
                this.fillSelectors(
                    "form#config-edit-form",
                    {'input[name="groups[hipay_credentials][fields][api_username_test][value]"]': credentials}
                    , false
                );
            }
            this.wait(
                500, function () {
                    this.click("#save");
                }
            );

            this.then(
                function () {
                    this.waitForSelector(
                        ".message.message-success.success", function success()
                        {
                            this.echo("HiPay Enterprise credentials configuration done", "INFO");
                        }, function fail()
                        {
                            test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
                        }, 20000
                    );
                }
            );
        }
    );
};


/**
 * Configure Device Fingerprint options via formular
 *
 * @param test
 * @param state
 */
exports.setDeviceFingerprint = function setDeviceFingerprint(test, state, configuration)
{
    var valueFingerprint;
    casper.then(
        function () {
            configuration.goingToHiPayConfiguration(test);
        }
    ).then(
        function () {
            this.echo("Changing 'Device Fingerprint' field...", "INFO");
            valueFingerprint = this.evaluate(
                function () {
                    return document.querySelector('select[name="groups[configurations][fields][fingerprint_enabled][value]"]').value;
                }
            );
            if (valueFingerprint == state) {
                this.echo("Device Fingerprint configuration already done", "INFO");
            } else {
                this.fillSelectors(
                    "form#config-edit-form", {
                        'select[name="groups[configurations][fields][fingerprint_enabled][value]"]': state
                    }, false
                );
                this.click("#hipay_configurations-head");
                this.wait(
                    500, function () {
                        this.click("#save");
                    }
                );
            }
        }
    ).then(
        function () {
            if (valueFingerprint != state) {
                this.waitForSelector(
                    ".message.message-success.success", function success()
                    {
                    this.echo("HiPay Enterprise credentials configuration done", "INFO");
                    }, function fail()
                    {
                        test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
                    }, 20000
                );
            }
        }
    );
};

/**
 *
 * @param paymentType
 */
exports.orderResult = function orderResult(test, paymentType, order)
{
    casper.then(
        function () {
            this.echo("Checking order success...", "INFO");
            this.waitForUrl(
                /checkout\/onepage\/success/, function success(response)
                {
                    // With SlimerJS it's 302 and not 200
                    //test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                    test.assertExists('.checkout-onepage-success', response.status + " The order has been successfully placed with method " + paymentType + " !");
                    order.setId(false);
                }, function fail()
                {
                    this.echo("Success payment page doesn't exists. Checking for pending payment page...", 'WARNING');
                    this.waitForUrl(
                        /hipay\/checkout\/pending/, function success()
                        {
                            this.warn("OK. This order is in pending");
                            // With SlimerJS it's 302 and not 200
                            //test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                            test.assertExists('.hipay-checkout-pending', "The order has been successfully pended with method " + paymentType + " !");
                            order.setId(true);
                        }, function fail()
                        {
                            test.assertUrlMatch(/hipay\/checkout\/pending/, "Checkout result page exists");
                        }, 150000
                    );
                }, 150000
            );
        }
    );
};

/**
 *
 * @param status
 */
exports.checkNotifMagento = function (test, status) {
    try {
        test.assertExists(x('//div[@id="order_history_block"]/ul/li[contains(., "Status code: ' + status + '")][position()=last()]'), "Notification " + status + " captured !");
        var operation = casper.fetchText(x('//div[@id="order_history_block"]/ul/li[contains(., "Status code: ' + status + '")][position()=last()]'));
        test.assertNotEquals(operation.indexOf('completed'), -1, "Successful operation !");
    } catch (e) {
        if (String(e).indexOf('operation') != -1) {
            test.fail("Failure on status operation: '" + operation + "'");
        } else if (status != 117) {
            test.fail("Failure: Notification " + status + " not exists");
        }
    }
};

/**
 *
 * @param status
 */
exports.goToOrderDetails = function (test, orderId) {
    casper.then(
        function () {
            this.waitForSelector(
                '#menu-magento-sales-sales a span', function success()
                {
                    this.echo("Checking status notifications from Magento server...", "INFO");
                }, function fail()
                {
                    test.assertExists('#menu-magento-sales-sales a span', "Order tab exists");
                }
            );

            test.info("click on menu-magento-sales-sales a span");
            this.wait(
                1000, function () {
                    this.click('#menu-magento-sales-sales a span');
                }
            );

            test.info("click on .item-sales-order.level-2 a span");
            this.waitUntilVisible(
                '.item-sales-order.level-2 a span', function () {
                    this.click('.item-sales-order.level-2 a span');
                }, function fail()
                {
                    test.assertExists(this.visible('.item-sales-order.level-2 a span'), "Configuration menu exists");
                }, 30000
            );

            test.info("Select order in table");
            this.waitForSelector(
                x('//td[contains(., "' + orderId + '")]'), function success()
                {
                this.wait(
                    1000, function () {
                        this.click(x('//td[contains(., "' + orderId + '")]'));
                    }
                );
                }, function fail()
                {
                    test.assertExists(x('//td[contains(., "' + orderId + '")]'), "Order # " + orderId + " exists");
                }
            );
        }
    );
};


exports.setCasper = function setCasper(casperInstance)
{
    casper = casperInstance;
};
