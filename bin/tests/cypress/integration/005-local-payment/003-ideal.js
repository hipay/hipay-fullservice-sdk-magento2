/**
 * Functionality tested
 *  - Local payment with redirection
 */
describe('Pay by ideal', function () {

    /**
     * Before
     */
    before(function () {
        cy.configureAndActivatePaymentMethod("hipay_ideal");
        cy.get('.account-signout').click({force: true});
    });
    
    /**
     *  Paypal
     */
    it('Pay with Ideal', function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_ideal').click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        if (Cypress.env('completeProviderPayment')) {
            cy.payIdeal();
            cy.checkOrderRedirect();
        } else {
            cy.location('host', {timeout: 100000}).should('include', 'stage-secure-gateway');
            cy.get('#issuer_bank_id');
        }
    });

});
