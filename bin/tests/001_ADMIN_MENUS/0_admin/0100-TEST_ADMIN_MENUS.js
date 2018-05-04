casper.test.begin('Test Magento Admin Menus', function(test) {
	phantom.clearCookies();

    casper.start(baseURL + "admin/")
	.thenOpen(urlBackend, function() {
		this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Integration tab */
	.then(function() {
		this.echo("Open Integration nav", "INFO");
		this.waitForUrl(/maccount/, function success() {
			this.selectHashingAlgorithm("SHA1");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
    .thenOpen(urlBackend, function() {
    	this.logToBackend();
    })
    /* Check HiPay Split Payments menu */
    .then(function() {
    	this.echo("Checking HiPay Split Payments menu...", "INFO");
    	this.waitForUrl(/admin\/dashboard/, function success() {

			this.click('#menu-hipay-fullservicemagento-hipay-payment-menu a span');

			this.waitUntilVisible('.item-hipay-split-payment.level-1 a span', function () {
				this.click('.item-hipay-split-payment.level-1 a span');
			}, function fail() {
				test.assertExists(this.visible('.item-hipay-split-payment.level-1 a span'), "Configuration menu exists");
			}, 30000);

	    	this.waitForUrl(/admin\/hipay\/splitpayment/, function success() {
	    		test.assertTextExists('Split Payments', "HiPay Split Payments menu activated !");
	    	}, function fail() {
	    		test.assertUrlMatch(/admin\/hipay\/splitpayment/, "Split Payments admin page exists");
	    	}, 10000);
	    }, function fail() {
	    	test.assertUrlMatch(/admin\/dashboard/, "Dashboard admin page exists");
	    }, 10000);
    })
    /* Check HiPay Enterprise menu */
    .then(function() {
    	this.echo("Checking Hipay Enterprise menu...", "INFO");

		configuration.goingToHiPayConfiguration(test);
    })
    /* Check Payment Methods bloc count */
    .then(function() {
    	this.echo("Checking Payments Methods blocs...", "INFO");
    	this.click(x('//span[contains(., "Payment Methods")]'));
    	this.waitForSelector('#payment_us_other_payment_methods-head', function success() {
			test.assert(this.exists('#payment_us_hipay_cc-head') ,"Payments Methods blocs exists !");
    	}, function fail() {
    		test.assertExists('#payment_us_other_payment_methods-head', "Payment Methods admin page exists");
    	}, 10000)
    })
    .run(function() {
        test.done();
    });
});
