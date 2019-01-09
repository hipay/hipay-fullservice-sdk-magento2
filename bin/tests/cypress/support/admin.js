Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin');
    cy.wait(300);
    cy.get('#username').type("admin");
    cy.get('#login').type('admin123');
    cy.get('.action-login').click();
});
