/**********************************************************************************************
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 /**********************************************************************************************/

var paymentType = 'HiPay Enterprise Hosted Page',
  currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin(
  'Test Checkout ' + paymentType + ' with Iframe and ' + currentBrandCC,
  function (test) {
    phantom.clearCookies();

    casper
      .start(baseURL + 'admin/')
      /* Active HiPay CC payment method if default card type is not defined or is VISA */
      .thenOpen(baseURL + 'admin/', function () {
        adminMod.logToBackend(baseURL, admin_login, admin_passwd);
        method.configure(
          test,
          paymentType,
          'hosted',
          [
            'select[name="groups[hipay_hosted][fields][iframe_mode][value]"]',
            '0'
          ],
          configuration
        );
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
        checkoutMod.choosingPaymentMethod(test, 'hipay_hosted');
      })
      .then(function () {
        this.wait(500, function () {
          checkoutMod.clickPayButton();
        });
      })
      /* Fill payment formular inside iframe */
      .then(function () {
        this.waitForUrl(
          /payment\/web/,
          function success() {
            paymentLibHiPay.fillPaymentFormularByPaymentProduct(
              currentBrandCC,
              test
            );
          },
          function fail() {
            test.assertUrlMatch(/payment\/web/, 'Payment page exists');
          },
          30000
        );
      })
      .then(function () {
        adminMod.orderResult(test, paymentType, order);
      })
      .run(function () {
        test.done();
      });
  }
);
