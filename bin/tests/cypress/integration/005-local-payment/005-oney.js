/**
 * Functionality tested
 *  - Local payment with redirection
 */
describe('Pay by oney', function () {

    /**
     * Before
     */
    before(function () {
        cy.logToAdmin();
        cy.configureAndActivatePaymentMethod("hipay_facilypay3X");
        cy.configureAndActivatePaymentMethod("hipay_facilypay4X");
        cy.setOptionSendCart("1");
        cy.get('.account-signout').click({force: true});
    });

    /**
     *  Paypal
     */
    it('Pay with Oney 3X', function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_facilypay3X').should('not.exist');
        cy.clearCookies();
        cy.goToFront();
        cy.selectMultipleItemsAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_facilypay3X').click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.checkOrderRedirect();
        if (Cypress.env('completeProviderPayment')) {
            cy.payOney();
            cy.checkOrderRedirect();
        } else {
            cy.location('host', {timeout: 100000}).should('include', 'vad.oney.com');
        }
    });

});
