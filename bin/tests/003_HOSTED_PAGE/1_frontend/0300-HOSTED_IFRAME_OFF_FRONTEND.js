/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Page",
    currentBrandCC = typeCC;

casper.test.begin('Test Checkout ' + paymentType + ' with Iframe and ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active Hosted payment method with display iframe */
        .then(function () {
            this.logToBackend();
            method.proceed(test, paymentType, "hosted", ['select[name="groups[hipay_hosted][fields][iframe_mode][value]"]', '0']);
        })
        .thenOpen(baseURL, function () {
            this.waitUntilVisible('div.footer', function success() {
                this.selectItemAndOptions();
            }, function fail() {
                test.assertVisible("div.footer", "'Footer' exists");
            }, 10000);
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
        .then(function () {
            this.choosingPaymentMethod("hipay_hosted");
        })
        .then(function () {
            this.wait(500, function () {
                this.clickPayButton();
            });
        })
        /* Fill payment formular inside iframe */
        .then(function () {
            this.waitForUrl(/payment\/web/, function success() {
                this.fillPaymentFormularByPaymentProduct(currentBrandCC);
            }, function fail() {
                test.assertUrlMatch(/payment\/web/, "Payment page exists");
            }, 30000);
        })
        .then(function () {
            this.orderResult(paymentType);
        })
        .run(function () {
            test.done();
        });
});
