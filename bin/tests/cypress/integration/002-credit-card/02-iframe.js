describe('Pay by credit card iframe', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentMethods();
        cy.activatePaymentMethod('hipay_hosted');
        cy.get('#payment_us_hipay_hosted_iframe_mode').select("1", {force: true});
        cy.get('#save').click({force: true});
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        //     cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillShippingForm("FR");

        cy.get('#hipay_hosted').click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by visa', function () {
        cy.wait(10000);
        cy.get('#hipay_hosted-iframe').then(function ($iframe) {
            cy.payCcIframe($iframe, "visa_ok");
        });
        cy.checkOrderSuccess();
    });

    it('Pay by visa refused', function () {
        cy.wait(10000);
        cy.get('#hipay_hosted-iframe').then(function ($iframe) {
            cy.payCcIframe($iframe, "visa_refused");
        });
        cy.checkOrderCancelled();
    });
});



