describe('Check admin', function () {

    before(function () {
        cy.logToAdmin();

    });

    it('Tabs exist', function () {
        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu > [onclick="return false;"]').click({force: true});
        cy.get('.item-hipay-payment-profile > a').click({force: true});
        cy.location('pathname', {timeout: 50000}).should('include', '/hipay/paymentprofile/');

        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu > [onclick="return false;"]').click({force: true});
        cy.get('.item-hipay-split-payment > a').click({force: true});
        cy.location('pathname', {timeout: 50000}).should('include', '/hipay/splitpayment/');

        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu > [onclick="return false;"]').click({force: true});
        cy.get('.item-hipay-mapping-categories > a').click({force: true});
        cy.location('pathname', {timeout: 50000}).should('include', '/hipay/cartcategories/');

        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu > [onclick="return false;"]').click({force: true});
        cy.get('.item-hipay-mapping-shipping > a').click({force: true});
        cy.location('pathname', {timeout: 50000}).should('include', '/hipay/mappingshipping/');
    });

});
