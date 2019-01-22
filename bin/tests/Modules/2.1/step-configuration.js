var casper;
var x = require('casper').selectXPath;

exports.goingToHiPayConfiguration = function goingToHiPayConfiguration(test) {

    this.goingToConfiguration(test, "HiPay Fullservice");
};

exports.goingToHiPayMethodsConfiguration = function goingToHiPayMethodsConfiguration(test) {

    this.goingToConfiguration(test, "Payment Methods");
};

exports.goingToConfiguration = function goingToHiPayMethodsConfiguration(test, name) {

    casper.then(function () {

        this.echo("Going to " + name + " Configuration page ...", "INFO");
        this.wait(1000, function () {
            this.click('#menu-magento-backend-stores a span');
        });
    })
        .then(function () {
            this.waitUntilVisible('.item-system-config.level-2 a span', function () {
                this.click('.item-system-config.level-2 a span');
            }, function fail() {
                test.assertExists(this.visible('.item-system-config.level-2 a span'), "Configuration menu exists");
            }, 30000);
        })
        .then(function () {
            this.waitForUrl(/admin\/system_config/, function success() {
                this.wait(1000, function () {
                    this.click('#system_config_tabs div:nth-child(4) div');
                });
            }, function fail() {
                test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
            }, 10000);
        })
        .then(function () {
            this.wait(1000, function () {
                this.click(x('//span[contains(., "' + name + '")]'));
            });
        })
        .then(function () {
            this.waitForSelector(x('//span[text()="' + name + '"]'), function success() {
                test.assertTextExists(name, "HiPay Enterprise menu activated !");
            }, function fail() {
                test.assertExists(x('//span[text()="' + name + '"]'), "Hipay Enterprise admin page exists");
            }, 10000);
        })
};

exports.setCasper = function setCasper(casperInstance) {
    casper = casperInstance;
};
