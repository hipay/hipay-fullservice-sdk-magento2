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


/**
 * @param test
 */
exports.selectItemAndOptions = function selectItemAndOptions(test) {
    casper.then(function () {
        this.echo("Selecting item and its options...", "INFO");

        this.waitForSelector('ol.widget-product-grid>li:first-of-type img', function success() {
            this.click('ol.widget-product-grid>li:first-of-type img');
        }, function fail() {
            var altImg = this.getElementAttribute('ol.widget-product-grid>li:first-of-type img', 'alt');
            test.assertExists('ol.widget-product-grid>li:first-of-type img', "'" + altImg + "' image exists");
        });

        this.echo("Done", "INFO");
    });
};

/**
 * @param test
 */
exports.addItemGoCheckout = function addItemGoCheckout(test) {
    casper.then(function () {
        this.echo("Adding this item then, accessing to the checkout...", "INFO");

        this.waitForSelector("#product-addtocart-button", function success() {
            this.wait(1000, function () {
                this.click("#product-addtocart-button");
            });
            this.echo("Item added to cart", "INFO");
        }, function fail() {
            test.assertNotExists('.message-error.error.message', "Warning message not present on submitting formular");
            test.assertExists("#product-addtocart-button", "Submit button exists");
        });
        this.then(function () {
            this.wait(1000, function () {
                this.click('.action.showcart');
            })
        });
        this.then(function () {
            this.waitUntilVisible('#top-cart-btn-checkout', function () {
                this.click('#top-cart-btn-checkout');
            }, function fail() {
                test.assertExists("#top-cart-btn-checkout", "Checkout button exists");
            }, 7500);
        });
        this.then(function () {
            this.waitForSelector("#shipping-method-buttons-container", function success() {
                test.assertExists("#shipping-method-buttons-container", "Checkout button exists");
                this.echo("Proceed to checkout", "INFO");
            }, function fail() {
                test.assertExists("#shipping-method-buttons-container", "Checkout button exists");
            }, 7500);
        });
    });
};

/**
 * @param test
 */
exports.billingInformation = function billingInformation(test, country) {
    casper.then(function () {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("form#co-shipping-form", function success() {
            var adress = getAdressByCountry(country);

            this.fillSelectors('form.form-login', {
                'input[name="username"]': 'email@yopmail.com'
            }, false);
            this.fillSelectors('form#co-shipping-form', {
                'input[name="firstname"]': 'TEST',
                'input[name="lastname"]': 'TEST',
                'input[name="street[0]"]': adress['street'],
                'input[name="city"]': adress['city'],
                'input[name="postcode"]': adress['cp'],
                'select[name="country_id"]': adress['id_country'],
                'input[name="telephone"]': '0171000000'
            }, false);
            if (this.visible('select[name="region_id"]')) {
                this.fillSelectors('form#co-shipping-form', {
                    'select[name="region_id"]': adress['region']
                }, false);
            }
            this.echo("Done", "INFO");
        }, function fail() {
            test.assertExists("form#co-billing-form", "'Billing Information' formular exists");
        });
    });
};

/**
 *
 * @param country
 * @returns {{street: string, city: string, cp: string, region: string, id_country: number}}
 */
function getAdressByCountry(country) {
    var adress = {
        'street': '1249 Tongass Avenue, Suite B',
        'city': 'Ketchikan',
        'cp': '9901',
        'region': '2',
        'id_country': 'FR'
    };
    switch (country) {
        case "FR":
            adress['street'] = 'Rue de la paix';
            adress['city'] = 'PARIS';
            adress['cp'] = '75000';
            adress['region'] = '257';
            casper.echo("French Address", "COMMENT");
            break;
        case "BR":
            casper.echo("Brazilian Address", "COMMENT");
            break;
        case "NL":
            adress['street'] = 'Rue de la paix';
            adress['city'] = 'Amsterdam';
            adress['cp'] = '1000 AA';
            adress['region'] = '257';
            adress['id_country'] = 'NL';
            casper.echo("Netherlands Address", "COMMENT");
            break;
        default:
            casper.echo("US Address", "COMMENT");
    }

    return adress;
}

/**
 * @param test
 */
exports.shippingMethod = function shippingMethod(test) {
    casper.then(function () {
        this.echo("Filling 'Shipping Method' formular...", "INFO");
        this.waitUntilVisible('input#s_method_flatrate_flatrate', function success() {
            this.click('input#s_method_flatrate_flatrate');
            this.click("div#shipping-method-buttons-container>div>button");
            this.echo("Done", "INFO");
        }, function fail() {
            test.assertVisible("input#s_method_flatrate_flatrate", "'Shipping Method' formular exists");
        }, 35000);
    });
};

/**
 * @param test
 */
exports.fillStepPayment = function fillStepPayment(test) {
    casper.then(function () {

        var method_hipay;

        this.echo("Choosing payment method and filling 'Payment Information' formular with " + currentBrandCC + "...", "INFO");

        this.waitWhileVisible(".loading-mask", function success() {
            this.echo("Payment form loaded", "INFO");
        }, function fail() {
            test.assertVisible(".loading-mask", "'Payment form Loaded");
        }, 15000);

        this.then(function () {
            this.waitForSelector('#hipay_cc', function success() {
                method_hipay = "hipay_cc";
            }, function fail() {
                test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
            }, 15000);
        });

        this.then(function () {
            this.echo("Filling payment form", "INFO");
            this.wait(1000, function () {
                this.click('input#' + method_hipay);

                if (currentBrandCC == 'visa') {
                    fillFormMagentoCreditCard(parametersLibHiPay.cardsNumber.visa);
                }
                else if (currentBrandCC == 'cb' || currentBrandCC == "mastercard") {
                    fillFormMagentoCreditCard(parametersLibHiPay.cardsNumber.cb);
                }
                else if (currentBrandCC == 'visa_3ds') {
                    fillFormMagentoCreditCard(parametersLibHiPay.cardsNumber.visa_3ds);
                }
                else if (currentBrandCC == 'maestro') {
                    fillFormMagentoCreditCard(parametersLibHiPay.cardsNumber.maestro);
                }


            });
        });

        this.then(function () {
            this.wait(500, function () {
                clickPayButton();
                test.info("Done");
            });
        });
    });
};

/**
 *
 * @param card
 */
function fillFormMagentoCreditCard(card) {
    casper.fillSelectors('form#co-payment-form', {
        'input[name="payment[cc_owner]"]': "TEST TEST",
        'input[name="payment[cc_number]"]': card,
        'select[name="payment[cc_exp_month]"]': '2',
        'select[name="payment[cc_exp_year]"]': '2020',
        'input[name="payment[cc_cid]"]': '500'
    }, false);
}

function clickPayButton() {
    casper.echo("Click Pay or continue Button ...", "INFO");
    casper.click('.payment-method._active .actions-toolbar:not([style="display: none;"])>div>button.checkout');
}

exports.clickPayButton = clickPayButton;

exports.choosingPaymentMethod = function (test, method_hipay) {
    casper.then(function () {
        this.echo("Choosing payment method with " + method_hipay, "INFO");
        this.waitUntilVisible('#checkout-step-payment input#' + method_hipay, function success() {
            this.click('input#' + method_hipay);
            test.info("Done");
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 25000);
    });
};
