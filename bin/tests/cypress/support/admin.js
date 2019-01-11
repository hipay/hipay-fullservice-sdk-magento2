Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin');
    cy.wait(300);
    cy.get('#username').type("admin");
    cy.get('#login').type('admin123');
    cy.get('.action-login').click();
});

Cypress.Commands.add("goToPaymentMethods", () => {
    cy.visit('/admin/admin/system_config/edit/section/payment/');
    cy.get('#payment_us_other_payment_methods-head').click({force: true});
});

Cypress.Commands.add("activatePaymentMethod", (method) => {
    cy.get('#payment_us_' + method + '-head').click({force: true});
    cy.get('#payment_us_' + method + '_active').select("1", {force: true});
    cy.get('#payment_us_' + method + '_env').select("stage", {force: true});
    cy.get('#save').click({force: true});
});
