/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = typeCC,
    file_path="002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + currentBrandCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active HiPay CC payment method if default card type is not defined or is VISA */
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "cc");
    })
    .thenOpen(baseURL, function() {
        this.waitUntilVisible('div.footer', function success() {
            this.selectItemAndOptions();
        }, function fail() {
            test.assertVisible("div.footer", "'Footer' exists");
        }, 10000);
    },15000)
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    /* Fill steps payment */
    .then(function() {
        this.fillStepPayment();
    })
    .then(function() {
        this.orderResult(paymentType);

        /* Test it again with another card type */
        if (currentBrandCC == 'visa') {
            casper.testOtherTypeCC(file_path,'mastercard');
        }

        if (currentBrandCC == 'mastercard') {
            casper.testOtherTypeCC(file_path,'maestro');
        }
    })
    .run(function() {
        test.done();
    });
});
