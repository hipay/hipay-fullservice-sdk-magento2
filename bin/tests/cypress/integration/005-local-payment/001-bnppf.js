describe('Pay by bnppf', function () {

    /**
     *
     */
    before(function () {
        cy.configureAndActivatePaymentMethod("hipay_bnpp3X");
        cy.get('.account-signout').click();
        cy.configureAndActivatePaymentMethod("hipay_bnpp4X");
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
        cy.payBnppf();
        cy.checkOrderRedirect();
        cy.saveLastOrderId();
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
    it('pay 4xcb', function () {
        cy.get('#hipay_bnpp4X').click();
    });

});
