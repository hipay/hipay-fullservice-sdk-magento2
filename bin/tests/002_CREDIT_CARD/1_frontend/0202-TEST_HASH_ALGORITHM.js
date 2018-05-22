var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = typeCC;

casper.test.begin('Change Hash Algorithm ' + paymentType + ' with ' + typeCC, function (test) {
    phantom.clearCookies();

    casper.setFilter("page.confirm", function (msg) {
        this.echo("Confirmation message " + msg, "INFO");
        return true;
    });

    casper.start(baseURL)
        .thenOpen(urlBackend, function () {
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
        })
        /* Open Integration tab */
        .then(function () {
            this.echo("Open Integration nav", "INFO");
            this.waitForUrl(/maccount/, function success() {
                this.selectHashingAlgorithm("SHA512");
            }, function fail() {
                test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
            });
        })
        .then(function () {
            this.logToBackend();
        })
        .then(function () {
            configuration.goingToHiPayConfiguration(test);
        })
        .then(function () {
            this.echo("Synchronize Hashing Algorithm", "INFO");
            this.waitForSelector('button#hashing_algorithm_button', function success() {
                var current = this.evaluate(function () {
                    return document.querySelector('#hipay_hashing_algorithm_hashing_algorithm_test').value;
                });
                test.info("Initial Hashing Algorithm :" + current);
                if (current != 'SHA1') {
                    test.fail("Initial value is wrong for Hashing : " + current);
                }
                this.thenClick('button#hashing_algorithm_button', function () {

                    this.waitForSelector('aside.modal-popup.confirm._show', function () {
                        this.click("aside.modal-popup.confirm._show button.action-accept");
                    });
                });

                this.then(function () {
                    this.waitForSelector('button#hashing_algorithm_button', function success() {
                        var newHashingAlgo = this.evaluate(function () {
                            return document.querySelector('#hipay_hashing_algorithm_hashing_algorithm_test').value;
                        });
                        if (newHashingAlgo != 'SHA512') {
                            test.fail("Synchronize doesn't work : " + current);
                        } else {
                            test.info("Done");
                        }
                    })
                });
            }, function fail() {
                test.assertExists('button#hashing_algorithm_button', "Syncronize button exist");
            });
        })
        .thenOpen(baseURL, function () {
            this.waitUntilVisible('div.footer', function success() {
                this.selectItemAndOptions();
            }, function fail() {
                test.assertVisible("div.footer", "'Footer' exists");
            }, 10000);
        }, 15000)
        .then(function () {
            this.addItemGoCheckout();
        })
        .then(function () {
            this.billingInformation();
        })
        .then(function () {
            this.shippingMethod();
        })
        /* Fill steps payment */
        .then(function () {
            this.fillStepPayment();
        })
        .then(function () {
            this.orderResult(paymentType);

        })
        .thenOpen(urlBackend, function () {
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
        })
        .then(function () {
            cartID = casper.getOrderId();
            orderID = casper.getOrderId();
            this.processNotifications(true, false, true, false, "OGONE_DEV");
        })
        .thenOpen(urlBackend, function () {
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
        })
        /* Open Integration tab */
        .then(function () {
            this.echo("Open Integration nav", "INFO");
            this.waitForUrl(/maccount/, function success() {
                this.selectHashingAlgorithm("SHA1");
            }, function fail() {
                test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
            });
        })
        .run(function () {
            test.done();
        });
});
