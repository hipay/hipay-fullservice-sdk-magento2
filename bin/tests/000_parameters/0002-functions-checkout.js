/* Return 1D array from multiple dimensional array */

casper.test.begin('Functions Checkout', function(test) {

    /* Fill Step Payment */
	casper.fillStepPayment = function () {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + currentBrandCC + "...", "INFO");
        this.waitForSelector('#hipay_cc', function success() {
            method_hipay = "hipay_cc";
            this.wait(1000, function(){
                this.click('input#' + method_hipay);
                if(currentBrandCC == 'visa')
                    this.fillFormMagentoCreditCard(cardsNumber.visa);
                else if(currentBrandCC == 'cb' || currentBrandCC == "mastercard")
                    this.fillFormMagentoCreditCard(cardsNumber.cb);
                else if(currentBrandCC == 'visa_3ds' )
                    this.fillFormMagentoCreditCard(cardsNumber.visa_3ds);
                else if(currentBrandCC == 'maestro' )
                    this.fillFormMagentoCreditCard(cardsNumber.maestro);
                this.click(".payment-method._active .actions-toolbar>div>button.checkout");
                test.info("Done");
            });
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
            test.info("Initial credential for api_user_name was :" + initialCredential);
            this.fillFormHipayEnterprise(initialCredential);
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 15000);
    };

    /* Fill HiPayCC formular */
    casper.fillFormMagentoCreditCard = function(card) {
        this.fillSelectors('form#co-payment-form', {
            'input[name="payment[cc_owner]"]': "TEST TEST",
            'input[name="payment[cc_number]"]': card,
            'select[name="payment[cc_exp_month]"]': '2',
            'select[name="payment[cc_exp_year]"]': '2020',
            'input[name="payment[cc_cid]"]': '500'
        }, false);
    };

    /**
     *  Select payment method in magento checkout
     *
     * @param method_hipay
     */
    casper.choosingPaymentMethod = function(method_hipay) {
        this.echo("Choosing payment method with " + method_hipay, "INFO");
        this.waitUntilVisible('#checkout-step-payment div#payment-buttons-container>button', function success() {
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method_hipay);
            } else {
                this.click('#dt_' + method_hipay +'>input[name="payment[method]"]');
            }
            this.click("div#payment-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 25000);
    }



        casper.echo('Functions checkout loaded !', 'INFO');
    test.done();
});
