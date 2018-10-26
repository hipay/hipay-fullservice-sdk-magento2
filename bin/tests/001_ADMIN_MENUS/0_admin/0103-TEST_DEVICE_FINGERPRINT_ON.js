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

var currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test Magento With Device Fingerprint', function (test) {
    phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card",
        ioBB = "";

    casper.start(baseURL)
        .then(function () {
            this.clearCache();
        })
        .then(function () {
            if (this.visible('p[class="bugs"]')) {
                test.done();
            }
        })
        .thenOpen(baseURL + "admin/", function () {
            adminMod.logToBackend(baseURL,admin_login,admin_passwd);
            method.configure(test, paymentType, "cc", "", configuration);
        })
        /* Active device fingerprint */
        .then(function () {
            adminMod.setDeviceFingerprint(test, '1', configuration);
        })
        .thenOpen(baseURL, function() {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function() {
            checkoutMod.addItemGoCheckout(test);
        })
        .then(function() {
            checkoutMod.billingInformation(test, "FR");
        })
        .then(function() {
            checkoutMod.shippingMethod(test);
        })
        /* Check ioBB field during payment formular step */
        .then(function () {
            this.echo("Checking 'ioBB' field inside checkout page...", "INFO");
            this.waitForSelector('#hipay_cc', function success() {
                ioBB = this.getElementAttribute('input#ioBBFingerPrint', 'value');
                test.assert(this.exists('input#ioBB') && ioBB != "", "'ioBB' field is present and not empty !");
                checkoutMod.fillStepPayment(test,false, "hipay_cc",currentBrandCC, parametersLibHiPay);
            }, function fail() {
                test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
            }, 10000);
        })
        .then(function () {
            adminMod.orderResult(test, paymentType, order);
        })
        /* Access to BO TPP */
        .thenOpen(urlBackend, function () {
            backendLibHiPay.logToHipayBackend(test, loginBackend, passBackend);
        })
        .then(function () {
            backendLibHiPay.selectAccountBackend(test, "OGONE_DEV");
        })
        .then(function () {
            this.waitForUrl(/maccount/, function success() {
                    this.click('a.nav-transactions');
                    test.info("Done");
                }, function fail() {
                    test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
                },
                25000);
        })
        .then(function () {
            this.echo("Finding order # " + order.getId() + " in order list...", "INFO");
            this.waitForUrl(/manage/, function success() {
                this.evaluate(function (ID) {
                    document.querySelector('input#orderid').value = ID;
                    document.querySelector('input[name="submitorderbutton"]').click();
                }, order.getId());
                test.info("Done");
            }, function fail() {
                test.assertUrlMatch(/manage/, "Manage page exists");
            });
        })
        /* Check ioBB value from Customer Details order from BO TPP */
        .then(function () {
            this.echo("Opening Customer Details...", "INFO");
            this.waitForSelector('a[href="#customer-details"]', function success() {
                this.thenClick('a[href="#customer-details"]', function () {
                    this.wait(1000, function () {
                        var BOioBB = this.fetchText(x('//td[text()="Device Fingerprint"]/following-sibling::td/span')).split('.')[0];
                        test.assert(BOioBB != "" && BOioBB != "N/A" && ioBB.indexOf(BOioBB) != -1,
                            "'ioBB' is correctly present into transaction details of BackOffice TPP with value :" + BOioBB);
                    });
                });
            }, function fail() {
                test.assertExists('a[href="#customer-details"]', "Customer Details tab exists");
            });
        })
        .run(function () {
            test.done();
        });
});
