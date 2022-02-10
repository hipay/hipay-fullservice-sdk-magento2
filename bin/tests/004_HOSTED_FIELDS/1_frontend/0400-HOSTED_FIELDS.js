/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

/**********************************************************************************************
 *                       VALIDATION TEST METHOD : HOSTED FIELDS
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 /**********************************************************************************************/

var paymentType = 'HiPay Enterprise Credit Card Hosted Fields',
  currentBrandCC = utilsHiPay.getTypeCC(),
  file_path = '004_HOSTED_FIELDS/1_frontend/0400-HOSTED_FIELDS.js';

casper.test.begin(
  'Test Checkout ' + paymentType + ' and ' + currentBrandCC,
  function (test) {
    phantom.clearCookies();

    casper
      .start(baseURL)
      .then(function () {
        this.clearCache();
      })
      .thenOpen(baseURL + 'admin/', function () {
        adminMod.logToBackend(baseURL, admin_login, admin_passwd);
        method.configure(test, paymentType, 'hosted_fields', '', configuration);
      })
      .thenOpen(baseURL, function () {
        checkoutMod.selectItemAndOptions(test);
      })
      .then(function () {
        checkoutMod.addItemGoCheckout(test);
      })
      .then(function () {
        checkoutMod.billingInformation(test, 'FR');
      })
      .then(function () {
        checkoutMod.shippingMethod(test);
      })
      .then(function () {
        this.wait(1000, function () {
          checkoutMod.fillStepPayment(
            test,
            true,
            'hipay_hosted_fields',
            currentBrandCC,
            parametersLibHiPay
          );
        });
      })
      .then(function () {
        adminMod.orderResult(test, paymentType, order);
      })
      .run(function () {
        test.done();
      });
  }
);
