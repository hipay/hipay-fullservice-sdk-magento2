
describe('Pay by paypal', function () {



    /**
     * Before
     */
    before(function () {
        cy.configureAndActivatePaymentMethod("hipay_paypalapi");
        cy.get('.account-signout').click({force: true});
        cy.clearCookies();
        Cypress.Cookies.preserveOnce();
    });

    /**
     * Before Each
     */
    beforeEach(function () {
        cy.fixture('url').as("url");
    });

    /**
     *  Paypal
     */
    it('Prepare Pay with Paypal', function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_paypalapi').click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.location('host', {timeout: 100000}).should('include', 'sandbox.paypal');
        cy.location('href').then((location) => {
            this.url.paypal = location;
            cy.writeFile('cypress/fixtures/url.json', this.url);
        });
    });

    /**
     *  Paypal
     */
    it('Pay with Paypal', function () {
        cy.visit(this.url.paypal);
        cy.location('host', {timeout: 100000}).should('include', 'sandbox.paypal');
        if (Cypress.env('completeProviderPayment')) {
             cy.payPaypal();
             cy.checkOrderRedirect();
        } else {
            cy.location('host', {timeout: 100000}).should('include', 'sandbox.paypal');
        }
    });

});
