casper.test.begin('Test Magento Hipay Enterprise Config', function(test) {
	phantom.clearCookies();
	var fields = [];
    casper.start(baseURL + "admin/")
    .then(function() {
    	this.logToBackend();
    })
    /* Check HiPay Enterprise menu blocs */
    .then(function() {
    	this.echo("Accessing to Hipay Enterprise menu and checking blocs menu...", "INFO");
    	this.waitForUrl(/admin\/dashboard/, function success() {

			this.click('#menu-magento-backend-stores a span');

			this.waitUntilVisible('.item-system-config.level-2 a span', function () {
				this.click('.item-system-config.level-2 a span');
			}, function fail() {
				test.assertExists(this.visible('.item-system-config.level-2 a span'), "Configuration menu exists");
			}, 30000);

	    	this.waitForUrl(/admin\/system_config/, function success() {
	    		this.click(x('//span[contains(., "HiPay Fullservice")]'));
	    		this.waitForSelector("#hipay_hipay_credentials-head", function success() {
    				test.assertExists('div.section-config>div>a#hipay_hipay_credentials-head', "Normal configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_hipay_credentials_tokenjs-head', "Token JS configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_hashing_algorithm-head', "Hash algorithm configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_fraud_payment_review-head', "fraud review configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_fraud_payment_deny-head', "fraud deny configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_fraud_payment_accept-head', "fraud accept configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_configurations-head', "Others configuration activated !");
	    		}, function fail() {
	    			test.assertExists('#hipay_hipay_credentials-head', "Hipay Enterprise admin page exists");
	    		}, 10000);
	    	}, function fail() {
	    		test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
	    	}, 10000);
	    }, function fail() {
	    	test.assertUrlMatch(/admin\/dashboard/, "Dashboard admin page exists");
	    }, 10000);
    })
    .run(function() {
        test.done();
    });
});
