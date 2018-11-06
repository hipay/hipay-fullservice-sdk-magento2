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

var typeCC,
    casper;

exports.setTypeCC = function (cc) {
    typeCC = cc;
};

exports.getTypeCC = function () {
    return typeCC;
};

/**
 *
 * @param test
 * @param file
 * @param new_typeCC
 */
exports.testOtherTypeCC = function testOtherTypeCC(test, file, new_typeCC, pathHeader ) {
    casper.then(function () {
        this.echo("Configure Test other Type cc with " + new_typeCC + file, "INFO");
        if (new_typeCC && new_typeCC !== typeCC) {
            typeCC = new_typeCC;
            test.info("New type CC is configured and new test is injected");
            phantom.injectJs(file);
        } else if (this.cli.get('type-cc') === undefined) {
            if (typeCC === "visa") {
                typeCC = "mastercard";
                phantom.injectJs(file);
            }
            else {
                typeCC = "visa";
            }
        } else {
            typeCC = "visa";
        }
    });
};

exports.setCasper = function setCasper(casperInstance) {
    casper = casperInstance;
};

