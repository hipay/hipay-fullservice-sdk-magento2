/**
 * Functionality tested
 *  - Local payment with redirection
 */
describe('Pay by ideal', function () {

    /**
     * Before
     */
    before(function () {
        //cy.configureAndActivatePaymentMethod("hipay_ideal");
        //cy.get('.account-signout').click({force: true});
        cy.clearCookies();
        Cypress.Cookies.preserveOnce();
    });

    /**
     * Before Each
     */
    beforeEach(function () {
        cy.fixture('url').as("url");
    });

    Cypress.on('window:before:load', (win) => {
        Object.defineProperty(win, 'self', {
            get: () => {
                return window.top
            }
        })
    })

    
    /**
     *  Paypal
     */
    it('Prepare Pay with Ideal', function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_ideal',{timeout:50000}).click();
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.location('host', {timeout: 100000}).should('include', 'stage-secure-gateway');
        cy.location('href').then((location) => {
            this.url.ideal = location;
            cy.writeFile('cypress/fixtures/url.json', this.url);
        });
    });



    /**
     *  Paypal
     */
    it('Pay with Ideal', function () {
        if (Cypress.env('completeProviderPayment')) {
            cy.on('window:before:unload', (e) => {
                console.log(window.location.href);
            });
            cy.server();
            cy.route('POST','**/payment/web/pay/**').as('getMollieRedirection');
            cy.get('#issuer_bank_id').select("INGBNL2A",{force: true});
            cy.get('#submit-button').click();
            cy.wait('@getMollieRedirection',{timeout:50000});
            cy.location('host', {timeout: 100000}).should('include', 'stage-secure-gateway');
            cy.visit(this.url.ideal, {
                onBeforeLoad: (win) => {
                    Object.defineProperty(win, 'self', {
                        get: () => {
                            return window.top
                        }
                    })
                }
            });
            cy.get('input[name="final_state"]').click();
            cy.get('#footer > button').click();
            cy.checkOrderRedirect();
        } else {
            cy.location('host', {timeout: 100000}).should('include', 'stage-secure-gateway');
            cy.get('#issuer_bank_id');
        }
    });

});
