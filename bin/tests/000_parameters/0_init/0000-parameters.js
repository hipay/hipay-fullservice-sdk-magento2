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
var fs = require('fs');
require.paths.push(fs.workingDirectory+'/bin/tests/Modules');
require.paths.push(fs.workingDirectory+'/bin/tests/Modules/2.1');
require.paths.push(fs.workingDirectory+'/bin/tests/000_lib/node_modules/');

var x = require('casper').selectXPath,
    defaultViewPortSizes = { width: 1920, height: 1080 },
    headerModule = "../../Modules/",
    headerLib = "../../000_lib/node_modules/hipay-casperjs-lib/",
    utilsHiPay = require('utils-casper'),
    order = require('order'),
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
        amex:"AE",
        maestro: "MI"
    },
    adminMod = require('2.1/admin'),
    checkoutMod = require('2.1/checkout'),
    method = require('2.1/step-config-method'),
    configuration = require('2.1/step-configuration'),
    currentCurrency = allowedCurrencies[0],
    parametersLibHiPay = require('hipay-casperjs-lib/0000-parameters'),
    paymentLibHiPay = require('hipay-casperjs-lib/0001-functions-payment'),
    backendLibHiPay = require('hipay-casperjs-lib/0002-functions-backend'),
    notificationLibHiPay = require('hipay-casperjs-lib/0003-functions-notification'),
    utilsLibHiPay = require('hipay-casperjs-lib/0004-utils');

casper.test.begin('Parameters', function(test) {
    baseURL = casper.cli.get('url');
    loginBackend = casper.cli.get('login-backend');
    passBackend = casper.cli.get('pass-backend');
    loginPaypal = casper.cli.get('login-paypal');
    passPaypal = casper.cli.get('pass-paypal');

    urlGiftCardAction = casper.cli.get('gift-card-url');
    giftCardNumber =casper.cli.raw.get('gift-card-number');
    giftCardCvv = casper.cli.get('gift-card-cvv');

    backendLibHiPay.setCasper(casper);
    adminMod.setCasper(casper);
    paymentLibHiPay.setCasper(casper);
    backendLibHiPay.setCasper(casper);
    notificationLibHiPay.setCasper(casper);
    utilsLibHiPay.setCasper(casper);
    order.setCasper(casper);
    checkoutMod.setCasper(casper);
    method.setCasper(casper);
    configuration.setCasper(casper);
    utilsHiPay.setCasper(casper);

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


    // casper.on('remote.message', function(message) {
    //     this.echo('remote message caught: ' + message);
    // });
    //
    // casper.on("resource.error", function(resourceError) {
    //     this.echo("Resource error: " + "Error code: "+resourceError.errorCode+" ErrorString: "+resourceError.errorString+" url: "+resourceError.url+" id: "+resourceError.id, "ERROR");
    // });
    //
    // casper.on("page.error", function(msg, trace) {
    //     this.echo("Error: " + msg, "ERROR");
    // });
    //
    // casper.on('resource.received', function(resource) {
    //     var status = resource.status;
    //     if(status >= 400) {
    //     this.echo('Resource ' + resource.url + ' failed to load (' + status + ')');
    //     }
    // });

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
