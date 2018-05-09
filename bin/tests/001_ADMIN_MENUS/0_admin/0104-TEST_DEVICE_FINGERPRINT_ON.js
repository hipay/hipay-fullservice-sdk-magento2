var initialCredential,
    currentBrandCC = typeCC;

casper.test.begin('Test Magento With Device Fingerprint', function (test) {
    phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card",
        ioBB = "";

    casper.start(baseURL)
        .then(function () {
            if (this.visible('p[class="bugs"]')) {
                test.done();
            }
        })
        .thenOpen(baseURL + "admin/", function () {
            this.logToBackend();
            method.proceed(test, paymentType, "cc");
        })
        /* Active device fingerprint */
        .then(function () {
            this.setDeviceFingerprint('1', test);
        })
        .thenOpen(baseURL, function () {
            this.selectItemAndOptions();
        })
        .then(function () {
            this.addItemGoCheckout();
        })
        .then(function () {
            this.billingInformation();
        })
        .then(function () {
            this.shippingMethod();
        })
        /* Check ioBB field during payment formular step */
        .then(function () {
            this.echo("Checking 'ioBB' field inside checkout page...", "INFO");
            this.waitForSelector('#hipay_cc', function success() {
                ioBB = this.getElementAttribute('input#ioBBFingerPrint', 'value');
                test.assert(this.exists('input#ioBBFingerPrint') && ioBB != "", "'ioBB' field is present and not empty !");
                this.fillStepPayment();
            }, function fail() {
                test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
            }, 10000);
        })
        .then(function () {
            this.orderResult(paymentType);
        })
        /* Access to BO TPP */
        .thenOpen(urlBackend, function () {
            orderID = this.getOrderId();
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
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
            this.echo("Finding order # " + orderID + " in order list...", "INFO");
            this.waitForUrl(/manage/, function success() {
                this.evaluate(function (ID) {
                    document.querySelector('input#orderid').value = ID;
                    document.querySelector('input[name="submitorderbutton"]').click();
                }, orderID);
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
