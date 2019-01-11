describe('Pay by credit card hosted page', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentMethods();
        cy.activatePaymentMethod('hipay_hosted');
        cy.get('#payment_us_hipay_hosted_iframe_mode').select("0", {force: true});
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
        cy.payCcHosted("visa_ok");
        cy.checkOrderSuccess();
    });

    it('Pay by visa refused', function () {
        cy.payCcHosted("visa_refused");
        cy.checkOrderCancelled();
    });
});



