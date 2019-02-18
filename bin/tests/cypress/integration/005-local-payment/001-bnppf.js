describe('Pay by bnppf', function () {

    /**
     *
     */
    before(function () {
        cy.logToAdmin();
        cy.configureAndActivatePaymentMethod("hipay_bnpp3X");
        cy.configureAndActivatePaymentMethod("hipay_bnpp4X");
        cy.get('.account-signout').click({force: true});
    });

    /**
     *
     */
    beforeEach(function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
    });

    /**
     *
     */
    afterEach(() => {
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        if (Cypress.env('completeProviderPayment')) {
            cy.payBnppf();
            cy.checkOrderRedirect();
        } else {
            cy.location('pathname', {timeout: 100000}).should('include', '/souscription.do');
        }
    });

    /**
     *  Bnppf 3xcb
     */
    it('Pay Bnpp 3xcb', function () {
        cy.get('#hipay_bnpp3X').click();
    });

    /**
     *  Bnppf 4xcb
     */
    it('Pay Bnpp 4xcb', function () {
        cy.get('#hipay_bnpp4X').click();
    });

});
