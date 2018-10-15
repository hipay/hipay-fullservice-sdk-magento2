/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = utilsHiPay.getTypeCC(),
    file_path = "002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active HiPay CC payment method if default card type is not defined or is VISA */
        .thenOpen(baseURL + "admin/", function () {
            adminMod.logToBackend(test);
            method.configure(test, paymentType, "cc");
        })
        .thenOpen(baseURL, function () {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function () {
            checkoutMod.addItemGoCheckout(test);
        })
        .then(function () {
            checkoutMod.billingInformation(test, "FR");
        })
        .then(function () {
            checkoutMod.shippingMethod(test);
        })
        /* Fill steps payment */
        .then(function () {
            checkoutMod.fillStepPayment(test);
        })
        .then(function () {
            adminMod.orderResult(test, paymentType);

            /* Test it again with another card type */
            if (currentBrandCC == 'visa') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'mastercard');
            }

            if (currentBrandCC == 'mastercard') {
                casper.testOtherTypeCC(file_path, 'maestro');
            }
        })
        .run(function () {
            test.done();
        });
});
