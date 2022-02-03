/**********************************************************************************************
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 /**********************************************************************************************/
var fs = require('fs');
var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = utilsHiPay.getTypeCC(),
    file_path = fs.workingDirectory + "/bin/tests/002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js";

casper.test.begin(
    'Test Checkout ' + paymentType + ' with ' + currentBrandCC,
    function (test) {
        phantom.clearCookies();

        casper.start(baseURL + "admin/")
        /* Active HiPay CC payment method if default card type is not defined or is VISA */
        .thenOpen(
            baseURL + "admin/",
            function () {
                adminMod.logToBackend(baseURL,admin_login,admin_passwd);
                method.configure(test, paymentType, "cc", "", configuration);
            }
        )
        .thenOpen(
            baseURL,
            function () {
                checkoutMod.selectItemAndOptions(test);
            }
        )
        .then(
            function () {
                checkoutMod.addItemGoCheckout(test);
            }
        )
        .then(
            function () {
                checkoutMod.billingInformation(test, "FR");
            }
        )
        .then(
            function () {
                checkoutMod.shippingMethod(test);
            }
        )
        /* Fill steps payment */
        .then(
            function () {
                checkoutMod.fillStepPayment(test,false, "hipay_cc", currentBrandCC, parametersLibHiPay);
            }
        )
        .then(
            function () {
                adminMod.orderResult(test, paymentType, order);

                /* Test it again with another card type */
                if (currentBrandCC == 'visa') {
                    utilsHiPay.testOtherTypeCC(test, file_path, 'mastercard',pathHeader);
                }

                if (currentBrandCC == 'mastercard') {
                    casper.testOtherTypeCC(file_path, 'maestro', pathHeader);
                }
            }
        )
        .run(
            function () {
                test.done();
            }
        );
    }
);
