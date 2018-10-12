var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Send Notification to Magento from TPP BackOffice via ' + paymentType + ' with ' + utilsHiPay.getTypeCC(), function (test) {
    phantom.clearCookies();

    /* Open URL to BackOffice HiPay TPP */
    casper.start(baseURL)
        .thenOpen(urlBackend, function () {
            backendLibHiPay.logToHipayBackend(test, loginBackend, passBackend);
        })
        .then(function () {
            backendLibHiPay.selectAccountBackend(test, "OGONE_DEV");
        })
        .then(function () {
            notificationLibHiPay.processNotifications(
                test,
                order.getId(),
                true,
                false,
                true,
                false,
                "OGONE_DEV",
                backendLibHiPay,
                loginBackend,
                passBackend,
                baseURL,
                urlNotification
            );
        })
        .thenOpen(baseURL + "admin/", function () {
            adminMod.logToBackend(test);
        })
        /* Open Magento admin panel and access to details of this order */
        .then(function () {
            adminMod.goToOrderDetails(test, order.getId());
        })
        .then(function () {
            test.info("Check order history");
            this.waitForSelector('div#order_history_block', function success() {
                /* Check notification with code 116 from Magento server */
                adminMod.checkNotifMagento(test, "116");
            }, function fail() {
                test.assertExists('div#order_history_block', "History block of this order exists");
            });
        })
        /* Idem Notification with code 117 */
        .then(function () {
            adminMod.checkNotifMagento(test, "117");
        })
        /* Idem Notification with code 118 */
        .then(function () {
            adminMod.checkNotifMagento(test, "118");
        })
        .run(function () {
            test.done();
        });
});
