exports.checkMail = function checkMail(test, paymentType) {
    /* Mail checkout and order payment */
    casper.thenOpen(urlMailCatcher, function() {
        var link =  "";
        this.echo("Checking last caught mail and paying order...", "INFO");
        this.waitForSelector('nav#messages tbody tr:first-child', function success() {
            this.click('nav#messages tr:first-child td');
            this.withFrame(0, function() {
                this.waitForSelector('a#pay_order', function success() {
                    link = this.getElementAttribute('a#pay_order', 'href');
                    test.info("Mail checked");
                }, function fail() {
                    test.assertExists(x('//a[text()=" Je paye ma commande maintenant!"]'));
                });
            });
            this.then(function() {
                this.thenOpen(link, function() {
                    this.waitForUrl(/payment\/web\/pay/, function success() {
                        pay.proceed(test);
                        this.then(function() {
                            this.echo("Checking order success...", "INFO");
                            this.waitForUrl(/checkout\/onepage\/success/, function success() {
                                test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                                test.assertExists('.checkout-onepage-success', "The order has been successfully placed with method " + paymentType + " !");
                            }, function fail() {
                                test.assertUrlMatch(/checkout\/onepage\/success/, "Checkout result page exists");
                            }, 20000);
                        });
                    }, function fail() {
                        test.assertUrlMatch(/payment\/web\/pay/, "Hosted payment page exists");
                    });
                });
            });
        }, function fail() {
            test.assertExists('nav#messages tbody tr:first-child', "Caught mails list exists and is not empty");
        }, 10000);
    });
};
