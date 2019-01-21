Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin');
    cy.wait(300);
    cy.get('#username').type("admin");
    cy.get('#login').type('admin123');
    cy.get('.action-login').click();
});

Cypress.Commands.add("goToPaymentMethods", () => {
    cy.get('#menu-magento-backend-stores').click();
    cy.get('.item-system-config > a').click();
    cy.get('.admin__page-nav-link.item-nav > span',{timeout: 50000}).contains("Payment Methods").click({force: true});
    cy.get('#payment_us_other_payment_methods-head',{timeout: 50000}).click({force: true});
});

Cypress.Commands.add("activatePaymentMethod", (method) => {
    cy.get('#payment_us_' + method + '-head').click({force: true});
    cy.get('#payment_us_' + method + '_active').select("1", {force: true});
    cy.get('#payment_us_' + method + '_env').select("stage", {force: true});
    cy.get('#save').click({force: true});
});


Cypress.Commands.add("configureAndActivatePaymentMethod", (method) => {
    cy.logToAdmin();
    cy.goToPaymentMethods();
    cy.activatePaymentMethod(method);
    cy.get('#save',{timeout: 70000}).click({force: true});
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
    cy.logToAdmin();
    cy.goToGeneralConfiguration();
    cy.get("#hipay_configurations_basket_enabled").select(value);
    cy.get('#save').click();
});

/**
 * Go To Hipay General configuration
 */
Cypress.Commands.add("goToGeneralConfiguration", () => {
    cy.get('#menu-magento-backend-stores').click();
    cy.get('.item-system-config > a').click();
    cy.get('.admin__page-nav-link.item-nav > span',{timeout: 50000}).contains("HiPay Fullservice").click({force: true});
});



