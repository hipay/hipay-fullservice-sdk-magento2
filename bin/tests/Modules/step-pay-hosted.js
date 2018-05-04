exports.proceed = function proceed(test, iframe) {

    /* Check template formular and choose card type */
    casper.then(function() {
        this.echo("Filling hosted payment formular...", "INFO");
        if(!iframe)
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
        this.waitForSelector('input#payment-product-switcher-visa', function success() {
            this.evaluate(function() {
                document.querySelector('input#payment-product-switcher-visa').click();
            });
            this.fillPaymentFormularByPaymentProduct("visa");
        }, function fail() {
            this.echo("VISA input doesn't exists. Checking for select field...", 'WARNING');
            this.waitForSelector('select#payment-product-switcher', function success() {
                this.warn("OK. This payment template is deprecated");
                this.fillSelectors('#form-payment', {
                    'select[name="paymentproductswitcher"]': "visa"
                });
                this.fillPaymentFormularByPaymentProduct("visa");
            }, function fail() {
                test.assertExists('select#payment-product-switcher', "Select field exists");
            });
        });
    });
};
