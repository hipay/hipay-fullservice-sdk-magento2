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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

casper.test.begin('Test Magento Hipay Enterprise Config', function (test) {
    phantom.clearCookies();
    var fields = [];
    casper.start(baseURL + "admin/")
        .then(function () {
            adminMod.logToBackend(baseURL,admin_login,admin_passwd);
        })
        .then(function () {
            this.echo("Accessing to Hipay Enterprise menu and checking blocs menu...", "INFO");
            configuration.goingToHiPayConfiguration(test);
        })
        .then(function () {

            this.waitForSelector("#hipay_hipay_credentials-head", function success() {
                test.assertExists('div.section-config>div>a#hipay_hipay_credentials-head', "Normal configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_hipay_credentials_tokenjs-head', "Token JS configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_hashing_algorithm-head', "Hash algorithm configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_fraud_payment_review-head', "fraud review configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_fraud_payment_deny-head', "fraud deny configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_fraud_payment_accept-head', "fraud accept configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_configurations-head', "Others configuration activated !");
                test.assertExists('div.section-config>div>a#hipay_hipay_proxy_settings-head', "Proxy configuration activated !");
            }, function fail() {
                test.assertExists('#hipay_hipay_credentials-head', "Hipay Enterprise admin page exists");
            }, 10000);

        })
        .run(function () {
            test.done();
        });
});
