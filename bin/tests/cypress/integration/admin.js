describe('Check admin', function () {

    before(function () {
        cy.logToAdmin();

    });

    it('Tabs exist', function () {
        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu > [onclick="return false;"]').click();
        cy.get('.item-hipay-payment-profile > a');
        cy.get('.item-hipay-split-payment > a');
        cy.get('.item-hipay-mapping-categories > a');
        cy.get('.item-hipay-mapping-categories > a')
    });

    // it('Should save credentials', function () {
    //
    // });

});
