describe('Pay by credit card hosted page', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentMethods();
        cy.activatePaymentMethod('hipay_hosted_fields');
        cy.get('#save').click({force: true});
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        //     cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillShippingForm("FR");

        cy.get('#hipay_hosted_fields').click();
        cy.get('#hipay-field-cardHolder > iframe');
        cy.wait(3000);
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    ['visa_ok', 'mastercard_ok', 'cb_ok', 'american-express_ok'].forEach((card) => {
        it('Pay by : ' + card, function () {
            cy.fill_hostedfield_card(card);
            cy.get(".payment-method._active > .payment-method-content .actions-toolbar:visible button").click();
            cy.checkOrderSuccess();
        });
    });

    ['bcmc_ok', 'maestro_ok'].forEach((card) => {
        it('Pay by : ' + card + ' Unsupported', function () {
            cy.fill_hostedfield_card(card);
            cy.get(".payment-method._active > .payment-method-content .actions-toolbar:visible button").click();
            cy.checkUnsupportedPayment();
        });
    });

    it('Pay by visa refused', function () {
        cy.fill_hostedfield_card('visa_refused');
        cy.get(".payment-method._active > .payment-method-content .actions-toolbar:visible button").click();
        cy.checkOrderCancelled();
    });
});



