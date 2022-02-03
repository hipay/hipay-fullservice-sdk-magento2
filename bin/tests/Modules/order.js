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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

var id = 0;
var x = require('casper').selectXPath;

function setId(pending)
{
    id = getOrderIdFromPage(pending);
    casper.echo("Order Id : " + id, "COMMENT");
}

function getId()
{
    return id;
}

function getOrderIdFromPage(pending)
{
    var orderID;

    if (pending) {
        orderID = casper.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
    } else {
        var text = casper.fetchText(x('//p[contains(., "Your order # is:")]')).split(':')[1];
        orderID = text.substring(1, text.length - 1);
    }
    return orderID;
}
function setCasper(casperInstance)
{
    casper = casperInstance;
};

module.exports = {
    setId: setId,
    getId: getId,
    setCasper: setCasper
};
