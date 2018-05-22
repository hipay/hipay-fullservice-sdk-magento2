var fs = require('fs'),
	utils = require('utils'),
	childProcess = require("child_process"),
	spawn = childProcess.spawn,
	x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	baseURL = casper.cli.get('url'),
	urlMailCatcher = casper.cli.get('url-mailcatcher'),
	typeCC = casper.cli.get('type-cc'),
	loginBackend = casper.cli.get('login-backend'),
	passBackend = casper.cli.get('pass-backend'),
	loginPaypal = casper.cli.get('login-paypal'),
	passPaypal = casper.cli.get('pass-paypal'),
	countryPaypal = 'US',
	order = casper.cli.get('order'),
	orderID = 0,
	headerModule = "../../Modules/",
	urlBackend = "https://stage-merchant.hipay-tpp.com/default/auth/login",
	pathGenerator = 'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh',
	urlNotification = "index.php/hipay/notify/index",
	method = require(headerModule + 'step-config-method'),
    configuration = require(headerModule + 'step-configuration'),
    pathHeader = "bin/tests/",
    pathErrors = pathHeader + "errors/",
    allowedCurrencies = [
    	{ currency: 'EUR', symbol: '€' },
    	{ currency: 'USD', symbol: '$' }
    ],
	 cardsType = {
		visa:"VI",
		cb:"MC",
		amex:"AE",
	},
    currentCurrency = allowedCurrencies[0],
    generatedCPF = "373.243.176-26";

casper.test.begin('Parameters', function(test) {
	/* Set default viewportSize and UserAgent */
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

	/* Set default card type if it's not defined */
	if(typeof typeCC == "undefined"){
		typeCC = "visa";
	}

	/* Say if BackOffice TPP credentials are set or not */
	if(loginBackend != "" && passBackend != ""){
		test.info("Backend credentials set");
	}else{
		test.comment("No Backend credentials");
	}

	if(loginPaypal != "" && passPaypal != ""){
		test.info("PayPal credentials set");
	}else{
		test.comment("No PayPal credentials");
	}

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
