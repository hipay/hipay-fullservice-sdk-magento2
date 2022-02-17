var casper;
var x = require('casper').selectXPath;

exports.configure = function configure(
  test,
  method,
  nameField,
  option,
  configuration
) {
  var needConfig = true;

  /* Payment method configuration */
  casper
    .then(function () {
      configuration.goingToHiPayMethodsConfiguration(test);
    })
    .then(function () {
      var otherPaymentBlock = this.getElementAttribute(
        '#payment_us_other_payment_methods-head',
        'class'
      );
      if (otherPaymentBlock !== 'open') {
        this.wait(500, function () {
          test.info('Collapse bloc is closed. Try to expand it.');
          this.click('#payment_us_other_payment_methods-head');
        });
      } else {
        test.info(
          'Collapse bloc Other Payment Method is open. Try to expand it.'
        );
      }
    })
    .then(function () {
      this.waitForSelector(
        x('//a[text()="' + method + '"]'),
        function success() {
          var linkBlock = this.getElementAttribute(
            '#payment_us_hipay_' + nameField + '-head',
            'class'
          );
          if (linkBlock == '') {
            test.info('Collapse bloc is closed. Try to expand it.');
            this.wait(500, function () {
              this.click(x('//a[text()="' + method + '"]'));
            });
          } else {
            test.info('Collapse bloc is open.');
          }
        },
        function fail() {
          test.assertExists(
            x('//a[text()="' + method + '"]'),
            method + ' payment method exists'
          );
        }
      );
    })
    .then(function () {
      this.waitUntilVisible(
        'select[name="groups[hipay_' + nameField + '][fields][active][value]"]',
        function success() {
          var enable =
              'select[name="groups[hipay_' +
              nameField +
              '][fields][active][value]"]',
            debug =
              'select[name="groups[hipay_' +
              nameField +
              '][fields][debug][value]"]',
            test2 =
              'select[name="groups[hipay_' +
              nameField +
              '][fields][env][value]"]',
            valueEnable = this.evaluate(function (el) {
              return document.querySelector(el).value;
            }, enable),
            valueDebug = this.evaluate(function (el) {
              return document.querySelector(el).value;
            }, debug),
            valueTest2 = this.evaluate(function (el) {
              return document.querySelector(el).value;
            }, test2);
          if (typeof option != 'undefined') {
            var valueOption0 = this.evaluate(function (el) {
              return document.querySelector(el).value;
            }, option[0]);
            if (
              valueEnable == 1 &&
              valueDebug == 1 &&
              valueTest2 == 1 &&
              valueOption0 == option[1]
            ) {
              needConfig = false;
            }
          } else {
            if (valueEnable == 1 && valueDebug == 1 && valueTest2 == 'stage') {
              needConfig = false;
            }
          }
          if (needConfig) {
            test.info('method ' + method + ' not activated');
            this.echo('Activating ' + method + '...', 'INFO');
            var fill = {};
            fill[enable] = '1';
            fill[debug] = '1';
            fill[test2] = 'stage';
            if (typeof option != 'undefined') {
              fill[option[0]] = option[1];
            }
            this.fillSelectors('form#config-edit-form', fill, false);
          } else {
            test.info(method + ' Configuration already done');
          }
        },
        function fail() {
          test.assertVisible(
            'select[name="groups[hipay_' +
              nameField +
              '][fields][active][value]"]',
            "'Enabled' select exists"
          );
        },
        80000
      );
    })
    .then(function () {
      if (needConfig) {
        this.wait(500, function () {
          this.click('#save');
        });
      }
    })
    .then(function () {
      if (needConfig) {
        this.waitForSelector(
          '.message.message-success.success',
          function success() {
            test.info(method + ' Configuration done');
          },
          function fail() {
            test.fail(
              'Failed to apply ' + method + ' Configuration on the system'
            );
          },
          40000
        );
      }
    });
};

exports.setCasper = function setCasper(casperInstance) {
  casper = casperInstance;
};
