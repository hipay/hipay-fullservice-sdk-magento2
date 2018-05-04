exports.proceed = function proceed(test, paymentType, method) {
    /* Store and customer selection */
    casper.then(function() {
        this.echo("Selecting customer in order to create a new order...", "INFO");
        this.click(x('//span[text()="Orders"]'));
        this.waitForSelector(x('//button/span/span/span[text()="Create New Order"]'), function success() {
            this.click(x('//button/span/span/span[text()="Create New Order"]'));
            this.waitForSelector(x('//tr[@title="136"]'), function success() {
                this.click(x('//tr[@title="136"]'));
                test.info("New order created for 'Jane Doe'");
            }, function fail() {
                test.assertExists(x('//tr[@title="136"]'), "Customer 'Jane Doe' exists");
            }, 20000);
        }, function fail() {
            test.assertExists(x('//button/span/span/span[text()="Create New Order"]'), "Create order button exists");
        }, 20000);
    })
    /* Language and product selection */
    .then(function() {
        this.echo("Selecting product for this order", "INFO");
        this.waitForSelector('input#store_2', function success() {

            this.evaluate(function() {
                document.querySelector('input#store_2').click();
                test.info("Select store");
            });

            this.waitForSelector(x('//span[text()="Add Products"]'), function success() {
                this.click(x('//span[text()="Add Products"]'));
                this.waitForSelector('#sales_order_create_search_grid_table tbody tr:first-child td:first-child', function success() {
                    this.click('#sales_order_create_search_grid_table tbody tr:first-child input.checkbox');
                    test.info('Product selected');
                }, function fail() {
                    test.assertExists("#sales_order_create_search_grid_table tbody tr:first-child td:first-child", "Product list exists and is not empty");
                });
                this.waitForSelector(x('//span[text()="Add Selected Product(s) to Order"]'), function success() {
                    this.click(x('//span[text()="Add Selected Product(s) to Order"]'));
                    test.info('Configuration selection done');
                }, function fail() {
                    test.assertExists(x('//span[text()="Add Selected Product(s) to Order"]'), "Add selected products button exists");
                });
            }, function fail() {
                test.assertExists(x('//span[text()="Add Products"]'), "Add products button exists");
            }, 25000);
        }, function fail() {
            test.assertExists('input#store_2', "Language input exists");
        }, 20000);
    })
    /* Shipping method selection */
    .then(function() {
        this.echo("Selecting shipping method...", "INFO");
        this.waitForSelector('#order-shipping-method-summary>a', function success() {
            this.click('#order-shipping-method-summary>a');
            this.waitForSelector('input#s_method_flatrate_flatrate', function success() {
                this.click('input#s_method_flatrate_flatrate');
                test.info("Shipping method selected");
            }, function fail() {
                test.assertExists('input#s_method_flatrate_flatrate', "Flat Rate shipping method exists");
            }, 30000);
        }, function fail() {
            test.assertExists('#order-shipping-method-summary>a', "Shipping method link exists");
        });
    })
    /* Payment method selection */
    .then(function() {
        this.echo("Selecting payment method...", "INFO");
        this.waitForSelector('#p_method_hipay_' + method, function success() {
            this.click('#p_method_hipay_' + method);
            test.info("Done");
        }, function fail() {
            test.assertExists('#p_method_hipay_' + method, paymentType + " payment method exists");
        });
    });
};
