describe('Pay by paypal', function () {

    /**
     * Before
     */
    before(function () {
        //cy.configureAndActivatePaymentMethod("hipay_paypalapi");
        //cy.get('.account-signout').click({force: true});
    });

    /**
     * Before Each
     */
    beforeEach(function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
    });


    /**
     *  Paypal
     */
    it('Pay with Paypal', function () {
        cy.get('#hipay_paypalapi').click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        if (Cypress.env('completeProviderPayment')) {
            cy.payPaypal();
            cy.checkOrderRedirect();
        } else {
            cy.location('host', {timeout: 100000}).should('include', 'sandbox.paypal');
        }
    });

});
