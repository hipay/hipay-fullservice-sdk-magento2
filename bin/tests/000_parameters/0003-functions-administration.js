/* Return 1D array from multiple dimensional array */

casper.test.begin('Functions Administration', function(test) {

    casper.selectHashingAlgorithm = function(hashing) {
        this.click('a.nav-integration');
        this.waitForSelector('div.box-content a:nth-child(3)', function success() {
            this.thenClick('div.box-content a:nth-child(3)', function() {
                this.waitForUrl(/security/, function success() {
                    this.echo("Selecting Hashing Algorithm", "INFO");
                    this.fillSelectors('form.form-vertical', {
                        'select[name="hash_algorithm"]': hashing,
                    }, false);
                    this.click('div.form-actions button[type="submit"]');

                    this.waitForText('Settings have been successfully updated', function success() {
                        test.info("Done");
                    }, function fail() {
                        test.assertExists('div.box-content a:nth-child(3)', "Security tab exists");
                    });
                }, function fail() {
                    test.assertUrlMatch(/security/, "Security page exists");
                });

            });
        }, function fail() {
            test.assertExists('div.box-content a:nth-child(3)', "Security tab exists");
        });
    };

    /* Log to MAGENTO Backend */
    casper.logToBackend = function () {
        /* Connection to prestashop admin panel */
        casper.thenOpen(baseURL + "/admin", function() {
            this.echo("Connecting to admin panel...", "INFO");
            this.waitForSelector("#login-form", function success() {
                this.fillSelectors('form#login-form', {
                    'input[name="login[username]"]': 'admin',
                    'input[name="login[password]"]': 'admin123'
                }, false);
                this.click('.action-login');
                this.waitForSelector("#menu-magento-backend-dashboard", function success() {
                    test.info("Done");
                }, function fail() {
                    test.assertExists(".message-error", "Incorrect credentials !");
                }, 20000);
            }, function fail() {
                this.waitForUrl(/admin\/dashboard/, function success() {
                    test.info("Already logged to admin panel !");
                }, function fail() {
                    test.assertUrlMatch(/admin\/dashboard/, "Admin dashboard exists");
                });
            });
        });
    };

    casper.setCurrencySetup = function(currency) {
        if(currency == 'BRL')
            this.echo("Changing currency setup...", "INFO");
        else
            this.echo("Reinitializing currency setup...", "INFO");
        this.click(x('//span[contains(., "Currency Setup")]'));
        this.waitForUrl(/section\/currency/, function success() {
            this.fillSelectors('form#config_edit_form', {
                'select[name="groups[options][fields][base][value]"]': currency,
                'select[name="groups[options][fields][default][value]"]': currency,
                'select[name="groups[options][fields][allow][value][]"]': currency
            }, false);
            this.click(x('//span[text()="Save Config"]'));
            this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                test.info("Currency Setup Configuration done");
            }, function fail() {
                test.fail('Failed to apply Currency Setup Configuration on the system');
            }, 15000);
        }, function fail() {
            test.assertUrlMatch(/section\/currency/, "Currency Setup page exists");
        }, 10000);
    };

    /* Test file again with another card type */
    casper.testOtherTypeCC = function(file,new_typeCC) {
        casper.then(function() {
            this.echo("Configure Test other Type cc with" + new_typeCC + file, "INFO");
            if (new_typeCC && new_typeCC != typeCC ) {
                typeCC = new_typeCC;
                test.info("New type CC is configured and new test is injected");
                phantom.injectJs(pathHeader + file);
            } else if(typeof this.cli.get('type-cc') == "undefined") {
                if(typeCC == "visa") {
                    typeCC = "mastercard";
                    phantom.injectJs(pathHeader + file);
                }
                else {
                    typeCC = "visa"; // retour du typeCC à la normale --> VISA pour la suite des tests
                }
            } else {
                typeCC = "visa";
            }
        });
    };

	casper.echo('Fonctions Adnimistration loaded !', 'INFO');
	test.info("Based URL: " + baseURL);
    test.done();
});
