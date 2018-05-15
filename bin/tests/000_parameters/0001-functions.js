casper.test.begin('Functions', function (test) {
    /* For each fails, show current successful tests and show current URL and capture image */
    var img = 0;
    test.on('fail', function () {
        img++;
        casper.echo("URL: " + casper.currentUrl, "WARNING");
        casper.capture(pathErrors + 'fail' + img + '.png');
        test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
        casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
    });

    /* Choose first item at home page */
    casper.selectItemAndOptions = function () {
        this.echo("Selecting item and its options...", "INFO");

        this.waitForSelector('ol.widget-product-grid>li:first-of-type img', function success() {
            this.click('ol.widget-product-grid>li:first-of-type img');
        }, function fail() {
            var altImg = this.getElementAttribute('ol.widget-product-grid>li:first-of-type img', 'alt');
            test.assertExists('ol.widget-product-grid>li:first-of-type img', "'" + altImg + "' image exists");
        });

        test.info("Done");
    };
    /* Add item and go to checkout */
    casper.addItemGoCheckout = function () {
        this.echo("Adding this item then, accessing to the checkout...", "INFO");

        this.waitForSelector("#product-addtocart-button", function success() {
            this.wait(1000, function () {
                this.click("#product-addtocart-button");
            });
            test.info('Item added to cart');
        }, function fail() {
            test.assertNotExists('.message-error.error.message', "Warning message not present on submitting formular");
            test.assertExists("#product-addtocart-button", "Submit button exists");
        });
        this.then(function () {
            this.wait(1000, function () {
                this.click('.action.showcart');
            })
        });
        this.then(function () {
            this.waitUntilVisible('#top-cart-btn-checkout', function () {
                this.click('#top-cart-btn-checkout');
            }, function fail() {
                test.assertExists("#top-cart-btn-checkout", "Checkout button exists");
            }, 7500);
        });
        this.then(function () {
            this.waitForSelector("#shipping-method-buttons-container", function success() {
                test.assertExists("#shipping-method-buttons-container", "Checkout button exists");
                test.info('Proceed to checkout');
            }, function fail() {
                test.assertExists("#shipping-method-buttons-container", "Checkout button exists");
            }, 7500);
        });
    };
    /* Fill billing operation */
    casper.billingInformation = function (country) {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("form#co-shipping-form", function success() {
            var street = '1249 Tongass Avenue, Suite B', city = 'Ketchikan', cp = '99901', region = '2';
            switch (country) {
                case "FR":
                    street = 'Rue de la paix';
                    city = 'PARIS';
                    cp = '75000';
                    region = '257';
                    test.comment("French Address");
                    break;
                case "BR":
                    test.comment("Brazilian Address");
                    break;
                default:
                    country = 'US';
                    test.comment("US Address");
            }
            this.fillSelectors('form.form-login', {
                'input[name="username"]': 'email@yopmail.com'
            }, false);
            this.fillSelectors('form#co-shipping-form', {
                'input[name="firstname"]': 'TEST',
                'input[name="lastname"]': 'TEST',
                'input[name="street[0]"]': street,
                'input[name="city"]': city,
                'input[name="postcode"]': cp,
                'select[name="country_id"]': country,
                'input[name="telephone"]': '0171000000'
            }, false);
            if (this.visible('select[name="region_id"]')) {
                this.fillSelectors('form#co-shipping-form', {
                    'select[name="region_id"]': region
                }, false);
            }
            test.info("Done");
        }, function fail() {
            test.assertExists("form#co-billing-form", "'Billing Information' formular exists");
        });
    };
    /* Fill shipping method */
    casper.shippingMethod = function () {
        this.echo("Filling 'Shipping Method' formular...", "INFO");
        this.waitUntilVisible('input#s_method_flatrate_flatrate', function success() {
            this.click('input#s_method_flatrate_flatrate');
            this.click("div#shipping-method-buttons-container>div>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("input#s_method_flatrate_flatrate", "'Shipping Method' formular exists");
        }, 35000);
    };

    /* Get order ID, if it exists, after purchase, and set it in variable */
    casper.setOrderId = function (pending) {
        if (pending)
            orderID = this.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
        else {
            var text = this.fetchText(x('//p[contains(., "Your order # is:")]')).split(':')[1];
            orderID = text.substring(1, text.length - 1);
        }
        test.info("Order ID : " + orderID);
    };
    /* Get order ID variable value */
    casper.getOrderId = function () {
        if (typeof order == "undefined" || order == "")
            return orderID;
        else
            return order;
    };
    /* Check order result */
    casper.orderResult = function (paymentType) {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(/checkout\/onepage\/success/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('.checkout-onepage-success', "The order has been successfully placed with method " + paymentType + " !");
            this.setOrderId(false);
        }, function fail() {
            this.echo("Success payment page doesn't exists. Checking for pending payment page...", 'WARNING');
            this.waitForUrl(/hipay\/checkout\/pending/, function success() {
                this.warn("OK. This order is in pending");
                test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                test.assertExists('.hipay-checkout-pending', "The order has been successfully pended with method " + paymentType + " !");
                this.setOrderId(true);
            }, function fail() {
                test.assertUrlMatch(/hipay\/checkout\/pending/, "Checkout result page exists");
            }, 50000);
        }, 50000);
    };

    /* Test file again with another currency */
    casper.testOtherCurrency = function (file) {
        casper.then(function () {
            if (currentCurrency == allowedCurrencies[0]) {
                currentCurrency = allowedCurrencies[1];
                phantom.injectJs(pathHeader + file);
            }
            else if (currentCurrency == allowedCurrencies[1])
                currentCurrency = allowedCurrencies[0]; // retour du currency à la normale --> EURO pour la suite des tests
        });
    };
    /* Configure HiPay Enterprise options via formular */
    casper.fillFormHipayEnterprise = function (credentials, moto) {
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
            this.fillSelectors("form#config-edit-form", {'input[name="groups[hipay_credentials_moto][fields][api_username_test][value]"]': credentials}, false);
        } else {
            this.fillSelectors("form#config-edit-form", {'input[name="groups[hipay_credentials][fields][api_username_test][value]"]': credentials}, false);
        }
        this.wait(500, function () {
            this.click("#save");
        });

        this.then(function () {
            this.waitForSelector(".message.message-success.success", function success() {
                test.info("HiPay Enterprise credentials configuration done");
            }, function fail() {
                test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
            }, 20000);
        });
    };

    /* Configure Device Fingerprint options via formular */
    casper.setDeviceFingerprint = function (state, test) {
        var valueFingerprint;
        casper.then(function () {
            configuration.goingToHiPayConfiguration(test);
        }).then(function () {
            this.echo("Changing 'Device Fingerprint' field...", "INFO");
            valueFingerprint = this.evaluate(function () {
                return document.querySelector('select[name="groups[configurations][fields][fingerprint_enabled][value]"]').value;
            });
            if (valueFingerprint == state) {
                test.info("Device Fingerprint configuration already done");
            } else {
                this.fillSelectors("form#config-edit-form", {
                    'select[name="groups[configurations][fields][fingerprint_enabled][value]"]': state
                }, false);
                this.click("#hipay_configurations-head");
                this.wait(500, function () {
                    this.click("#save");
                });
            }
        }).then(function () {
            if (valueFingerprint != state) {
                this.waitForSelector(".message.message-success.success", function success() {
                    test.info("HiPay Enterprise credentials configuration done");
                }, function fail() {
                    test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
                }, 20000);
            }
        });
    };

    casper.echo('Fonctions chargées !', 'INFO');
    test.info("Based URL: " + baseURL);
    test.done();
});
