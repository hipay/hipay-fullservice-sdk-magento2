Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin');
    cy.wait(300);
    cy.get('#username').type("admin");
    cy.get('#login').type('admin123');
    cy.get('.action-login').click();
});

Cypress.Commands.add("goToPaymentMethods", () => {
    cy.visit('/admin/admin/system_config/edit/section/payment/');
});

Cypress.Commands.add("activatePaymentMethod", (method) => {
    cy.get('#payment_us_' + method + '-head').click({force: true});
    cy.get('#payment_us_' + method + '_active').select("1", {force: true});
    cy.get('#payment_us_' + method + '_env').select("stage", {force: true});
    cy.get('#save').click({force: true});
});


Cypress.Commands.add("configureAndActivatePaymentMethod", (method) => {
    cy.goToPaymentMethods();
    cy.activatePaymentMethod(method);
    cy.get('#save', {timeout: 70000}).click({force: true});
});

/**
 *
 */
Cypress.Commands.add("goToDetailOrder", (orderId) => {
    cy.get("tr.data-row").contains(orderId).parent().parent().find(".data-grid-actions-cell a").click();
});

/**
 * Activate Option send cart
 */
Cypress.Commands.add("setOptionSendCart", (value) => {
    cy.goToGeneralConfiguration();
    cy.get("#hipay_configurations_basket_enabled").select(value, {force: true});
    cy.get('#save').click();
});

/**
 * Go To Hipay General configuration
 */
Cypress.Commands.add("goToGeneralConfiguration", () => {
    cy.visit('/admin/admin/system_config/edit/section/hipay/');
});



