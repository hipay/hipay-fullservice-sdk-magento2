/**
 * Functionality tested
 *  - Local payment with redirection
 */
const sddData = require('@hipay/hipay-cypress-utils/fixtures/payment-means/sdd.json');

describe('Pay by sdd', function () {

    /**
     * Before
     */
    before(function () {
        cy.configureAndActivatePaymentMethod("hipay_sdd");
        cy.get('.account-signout').click({force: true});
    });
    
    /**
     *  Paypal
     */
    it('Pay with Ideal', function () {
        cy.setOptionSendCart("0");
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_sdd').click();
        cy.get('#hipay_sdd_gender > .control > select').select("M");
        cy.get('.control > #hipay_sdd_firstname').type("Bertand");
        cy.get('.control > #hipay_sdd_lastname').type("Pichot");
        cy.get('.control > #hipay_sdd_iban').type("BAD VALUES");
        cy.get('.control > #hipay_sdd_code_bic').type("BAD VALUES");
        cy.get('.control > #hipay_sdd_bank_name').type("Bank name");
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.get("div.message-error").contains("Iban is not correct, please enter a valid Iban.");
        cy.get('.control > #hipay_sdd_iban').clear();
        cy.get('.control > #hipay_sdd_code_bic').clear();
        cy.get('.control > #hipay_sdd_iban').type(sddData.iban);
        cy.get('.control > #hipay_sdd_code_bic').type(sddData.bic);
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.checkOrderRedirect();
    });

});
