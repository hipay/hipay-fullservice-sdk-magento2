exports.proceed = function proceed(test, method, nameField, option) {

    var needConfig = true

    /* Payment method configuration */
    casper.then(function () {
        configuration.goingToHiPayMethodsConfiguration(test);
    })
        .then(function () {
            otherPaymentBlock = this.getElementAttribute('#payment_us_other_payment_methods-head', 'class');
            if(otherPaymentBlock !== "open" ){
                this.wait(500, function () {
                    test.info("Collapse bloc is closed. Try to expand it.");
                    this.click('#payment_us_other_payment_methods-head');
                });
            }
        })
        .then(function () {
            this.waitForSelector(x('//a[text()="' + method + '"]'), function success() {
                linkBlock = this.getElementAttribute('#payment_us_hipay_' + nameField + '-head', 'class');
                if (linkBlock == "") {
                    test.info("Collapse bloc is closed. Try to expand it.");
                    this.wait(500, function () {
                        this.click(x('//a[text()="' + method + '"]'));
                    });
                }

            }, function fail() {
                test.assertExists(x('//a[text()="' + method + '"]'), method + " payment method exists");
            });
        })
        .then(function () {
            this.waitUntilVisible('select[name="groups[hipay_' + nameField + '][fields][active][value]"]', function success() {
                    var enable = 'select[name="groups[hipay_' + nameField + '][fields][active][value]"]',
                        debug = 'select[name="groups[hipay_' + nameField + '][fields][debug][value]"]',
                        test2 = 'select[name="groups[hipay_' + nameField + '][fields][env][value]"]',
                        valueEnable = this.evaluate(function (el) {
                            return document.querySelector(el).value;
                        }, enable),
                        valueDebug = this.evaluate(function (el) {
                            return document.querySelector(el).value;
                        }, debug),
                        valueTest2 = this.evaluate(function (el) {
                            return document.querySelector(el).value;
                        }, test2);
                    if (typeof option != 'undefined') {
                        var valueOption0 = this.evaluate(function (el) {
                            return document.querySelector(el).value;
                        }, option[0]);
                        if (valueEnable == 1 && valueDebug == 1 && valueTest2 == 1 && valueOption0 == option[1]) {
                            needConfig = false;
                        }
                    } else {
                        if (valueEnable == 1 && valueDebug == 1 && valueTest2 == "stage") {
                            needConfig = false;
                        }
                    }
                    if (needConfig) {
                        test.info("method " + method + " not activated");
                        this.echo("Activating " + method + "...", "INFO");
                        var fill = {};
                        fill[enable] = "1";
                        fill[debug] = "1";
                        fill[test2] = "stage";
                        if (typeof option != 'undefined')
                            fill[option[0]] = option[1];
                        this.fillSelectors('form#config-edit-form', fill, false);
                    } else {
                        test.info(method + " Configuration already done");
                    }
                }, function fail() {
                    test.assertVisible('select[name="groups[hipay_' + nameField + '][fields][active][value]"]', "'Enabled' select exists");
                },
                30000);
        })
        .then(function () {
            if (needConfig) {
                this.wait(500, function () {
                    this.click('#save');
                });
            }
        })
        .then(function () {
            if (needConfig) {
                this.waitForSelector('.message.message-success.success', function success() {
                    test.info(method + " Configuration done");
                }, function fail() {
                    test.fail('Failed to apply ' + method + ' Configuration on the system');
                }, 30000);
            }
        })
};
