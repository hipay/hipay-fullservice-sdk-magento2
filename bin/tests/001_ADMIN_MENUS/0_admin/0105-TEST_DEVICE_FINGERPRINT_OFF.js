var initialCredential,
    currentBrandCC = typeCC;

casper.test.begin('Test Magento Without Device Fingerprint', function (test) {
    phantom.clearCookies();
    var ioBB = "";
    casper.start(baseURL)
        .then(function () {
            if (this.visible('p[class="bugs"]')) {
                test.done();
            }
        })
        .thenOpen(baseURL + "admin/", function () {
            this.logToBackend();
        })
        /* Disactive device fingerprint */
        .then(function () {
            this.setDeviceFingerprint('0', test);
        })
        .thenOpen(baseURL, function () {
            this.selectItemAndOptions();
        })
        .then(function () {
            this.addItemGoCheckout();
        })
        /* Check no ioBB field */
        .then(function () {
            this.waitForUrl(/checkout\/onepage/, function success() {
                this.echo("Checking 'ioBB' field NOT inside checkout page...", "INFO");
                test.assertDoesntExist('input#ioBB', "'ioBB' field is present and not empty !");
            }, function fail() {
                test.assertUrlMatch(/checkout\/onepage/, "Checkout page exists");
            }, 10000);
        })
        .run(function () {
            test.done();
        });
});
