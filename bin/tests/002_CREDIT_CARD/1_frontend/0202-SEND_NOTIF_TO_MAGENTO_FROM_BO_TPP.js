var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Send Notification to Magento from TPP BackOffice via ' + paymentType + ' with ' + typeCC, function (test) {
    phantom.clearCookies();
    var data = "",
        hash = "",
        output = "",
        notif117 = true,
        reload = false,
        orderID = casper.getOrderId(); // Get order ID from previous order or from command line parameter

    /* Check status notification from Magento server on the order */
    casper.checkNotifMagento = function (status) {
        try {
            test.assertExists(x('//div[@id="order_history_block"]/ul/li[contains(., "Status code: ' + status + '")][position()=last()]'), "Notification " + status + " captured !");
            var operation = this.fetchText(x('//div[@id="order_history_block"]/ul/li[contains(., "Status code: ' + status + '")][position()=last()]'));
            test.assertNotEquals(operation.indexOf('completed'), -1, "Successful operation !");
        } catch (e) {
            if (String(e).indexOf('operation') != -1){
                test.fail("Failure on status operation: '" + operation + "'");
            }else if (status != 117){
                test.fail("Failure: Notification " + status + " not exists");
            }
        }
    };

    /* Open URL to BackOffice HiPay TPP */
    casper.start(baseURL)
        .then(function () {
            this.waitUntilVisible('div.footer', function success() {
                if (this.exists('p[class="bugs"]')) {
                    test.done();
                    this.echo("Test skipped MAGENTO 1.8", "INFO");
                }
            }, function fail() {
                test.assertVisible("div.footer", "'Footer' exists");
            }, 10000);
        })
        .thenOpen(urlBackend, function () {
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
        })
        .then(function () {
            cartID = casper.getOrderId();
            orderID = casper.getOrderId();
            this.processNotifications(true, false, true, false, "OGONE_DEV");
        })
        /* Open Magento admin panel and access to details of this order */
        .thenOpen(baseURL + "admin/", function () {
            this.logToBackend();
            this.waitForSelector('#menu-magento-sales-sales a span', function success() {
                this.echo("Checking status notifications from Magento server...", "INFO");
            }, function fail() {
                test.assertExists('#menu-magento-sales-sales a span', "Order tab exists");
            });

            this.then(function () {
                test.info("click on menu-magento-sales-sales a span");
                this.wait(1000, function () {
                    this.click('#menu-magento-sales-sales a span');
                });
            });

            this.then(function () {
                test.info("click on .item-sales-order.level-2 a span");
                this.waitUntilVisible('.item-sales-order.level-2 a span', function () {
                    this.click('.item-sales-order.level-2 a span');
                }, function fail() {
                    test.assertExists(this.visible('.item-sales-order.level-2 a span'), "Configuration menu exists");
                }, 30000);
            });
            this.then(function () {
                test.info("Select order in table");
                this.waitForSelector(x('//td[contains(., "' + orderID + '")]'), function success() {
                    this.wait(1000, function () {
                        this.click(x('//td[contains(., "' + orderID + '")]'));
                    });
                }, function fail() {
                    test.assertExists(x('//td[contains(., "' + orderID + '")]'), "Order # " + orderID + " exists");
                });
            });
            this.then(function () {
                test.info("Check order history");
                this.waitForSelector('div#order_history_block', function success() {
                    /* Check notification with code 116 from Magento server */
                    this.checkNotifMagento("116");
                }, function fail() {
                    test.assertExists('div#order_history_block', "History block of this order exists");
                });
            });
        })
        /* Idem Notification with code 116 */
        .then(function () {
            this.checkNotifMagento("117");
        })
        /* Idem Notification with code 117 */
        .then(function () {
            this.checkNotifMagento("118");
        })
        .run(function () {
            test.done();
        });
});
