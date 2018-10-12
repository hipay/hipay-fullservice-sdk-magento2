/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

var x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	baseURL = casper.cli.get('url'),
	loginBackend = casper.cli.get('login-backend'),
	passBackend = casper.cli.get('pass-backend'),
	loginPaypal = casper.cli.get('login-paypal'),
	passPaypal = casper.cli.get('pass-paypal'),
	headerModule = "../../Modules/",
    headerLib = "../../000_lib/node_modules/hipay-casperjs-lib/",
    utilsHiPay = require(headerModule + 'utils'),
    order = require(headerModule + 'order'),
    admin_login = "admin",
    admin_passwd = "admin123",
	urlBackend = "https://stage-merchant.hipay-tpp.com/default/auth/login",
    urlNotification = "index.php/hipay/notify/index",
    pathHeader = "bin/tests/",
    pathErrors = pathHeader + "errors/",
    allowedCurrencies = [
    	{ currency: 'EUR', symbol: '€' },
    	{ currency: 'USD', symbol: '$' }
    ],
	 cardsType = {
		visa:"VI",
		cb:"MC",
		amex:"AE"
	},
    adminMod = require(headerModule + '2.1/admin'),
    checkoutMod = require(headerModule + '2.1/checkout'),
    method = require(headerModule + '2.1/step-config-method'),
    configuration = require(headerModule + '2.1/step-configuration'),
    currentCurrency = allowedCurrencies[0],
    parametersLibHiPay = require(headerLib + '0000-parameters'),
    paymentLibHiPay = require(headerLib + '0001-functions-payment'),
    backendLibHiPay = require(headerLib + '0002-functions-backend'),
    notificationLibHiPay = require(headerLib + '0003-functions-notification'),
    utilsLibHiPay = require(headerLib + '0004-utils');

casper.test.begin('Parameters', function(test) {

    var img = 0;
    test.on('fail', function () {
        img++;
        casper.echo("URL: " + casper.currentUrl, "WARNING");
        casper.capture(pathErrors + 'fail' + img + '.png');
        test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
        casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
    });

	/* Set default viewportSize and UserAgent */
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

    utilsHiPay.setTypeCC(casper.cli.get('type-cc'));

    if (utilsHiPay.getTypeCC() === undefined) {
        utilsHiPay.setTypeCC("visa");
    }

	/* Say if BackOffice TPP credentials are set or not */
    /* Say if BackOffice TPP credentials are set or not */
    if (loginBackend && passBackend) {
        test.info("Backend credentials set");
    } else {
        test.comment("No Backend credentials");
    }

    if (loginPaypal && passPaypal) {
        test.info("PayPal credentials set");
    } else {
        test.comment("No PayPal credentials");
    }

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
