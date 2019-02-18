/**
 * Functionality tested
 *  - Local payment with redirection
 */
import sddJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sdd.json';

describe('Pay by sdd', function () {

    /**
     * Before
     */
    before(function () {
        cy.logToAdmin();
        cy.configureAndActivatePaymentMethod("hipay_sdd");
        cy.setOptionSendCart("0");
        cy.get('.account-signout').click({force: true});
    });


    beforeEach(function () {
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillShippingForm("FR");
        cy.get('#hipay_sdd').click();
    });

    it('Wrong form fields SEPA Direct Debit', function () {

        cy.get('#hipay_sdd_gender > .control > select').select("M");
        cy.get('.control > #hipay_sdd_firstname').type("Bertand");
        cy.get('.control > #hipay_sdd_lastname').type("Pichot");
        cy.get('.control > #hipay_sdd_iban').type("BAD VALUES");
        cy.get('.control > #hipay_sdd_code_bic').type("BAD VALUES");
        cy.get('.control > #hipay_sdd_bank_name').type("Bank name");
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.get("div.message-error").contains("Iban is not correct, please enter a valid Iban.");
    });

    it('Pay by SEPA Direct Debit', function () {
        cy.get('#hipay_sdd_gender > .control > select').select("M");
        cy.get('.control > #hipay_sdd_firstname').type("Bertand");
        cy.get('.control > #hipay_sdd_lastname').type("Pichot");
        cy.get('.control > #hipay_sdd_bank_name').type(sddJson.data.bank_name);
        cy.get('.control > #hipay_sdd_iban').type(sddJson.data.iban);
        cy.get('.control > #hipay_sdd_code_bic').type(sddJson.data.bic);
        cy.get(".payment-method._active > .payment-method-content > .actions-toolbar:visible button").click();
        cy.checkOrderRedirect();
    });

});
