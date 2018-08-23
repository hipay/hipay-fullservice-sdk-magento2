var initialCredential,
    currentBrandCC = typeCC;

casper.test.begin('Test Payment With Incorrect Credentials', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card";

    casper.start(baseURL + "admin/")
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "cc");
    })
    /* Disactive MOTO option */
    .then(function() {
        configuration.goingToHiPayConfiguration(test);
    })
    /* Set bad credentials inside HiPay Entreprise formular */
    .then(function(){
        initialCredential = this.evaluate(function() { return document.querySelector('input[name="groups[hipay_credentials][fields][api_username_test][value]"]').value; });
        test.info("Initial credential for api_user_name was :" + initialCredential);
        this.fillFormHipayEnterprise("blabla");
    })
    .thenOpen(baseURL, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    /* HiPay CC payment */
    .then(function() {
        this.fillStepPayment();
    })
    /* Check failure page */
    .then(function() {
        this.echo("Checking order failure cause of incorrect credentials...", "INFO");
        this.waitForSelector('.message.message-error.error', function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertSelectorHasText(
                '.message.message-error.error',
                "\n        There was an error request new transaction: Incorrect Credentials : API User Not Found.\n    ",
                "Correct response from Magento server !"
            );
        }, function fail() {
            test.assertExists('.message.message-error.error', "Correct response from Magento server !");
        }, 15000);
    })
    .then(function() {
        this.logToBackend();
    })
    .then(function() {
        this.echo("Accessing to Hipay Enterprise menu...", "INFO");
        configuration.goingToHiPayConfiguration(test);
    })
    /* Reinitialize credentials inside HiPay Enterprise */
    .then(function() {
        test.info("Initial credential for api_user_name was :" + initialCredential);
        this.fillFormHipayEnterprise(initialCredential);
    })
    .run(function() {
        test.done();
    });
});
