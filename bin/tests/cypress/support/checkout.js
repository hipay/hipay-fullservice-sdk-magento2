Cypress.Commands.add("goToFront", () => {
    cy.visit('/');
});

Cypress.Commands.add("selectItemAndGoToCart", () => {
    cy.goToFront();

    cy.server();
    cy.route('/customer/section/load/**').as('getCustomerSection');

    cy.wait('@getCustomerSection');

    cy.get(':nth-child(1) > .product-item-info > .product-item-details > .product-item-actions > .actions-primary > .action').click();

    cy.wait('@getCustomerSection');
    cy.get('.showcart').click();
});

Cypress.Commands.add("addProductQuantity", (qty) => {

    cy.server();
    cy.route('/customer/section/load/**').as('getCustomerSection');

    cy.get('.product-item-pricing > .details-qty > .cart-item-qty').clear();
    cy.get('.product-item-pricing > .details-qty > .cart-item-qty').type(qty, {force: true});
    cy.get('.product-item-pricing > .details-qty > button').click();

    cy.wait('@getCustomerSection');
});

Cypress.Commands.add("goToCheckout", () => {
    cy.get('#top-cart-btn-checkout',{"timeout": 35000}).click({force: true});
});

Cypress.Commands.add("fillShippingForm", (country) => {

    cy.server();
    cy.route('POST', '/rest/default/V1/guest-carts/*/estimate-shipping-methods').as('getEstimateShippingMethods');
    cy.route('POST', '/rest/default/V1/guest-carts/*/shipping-information').as('postShippingInformation');
    cy.route('POST', '/rest/default/V1/customers/isEmailAvailable').as('isEmailAvailable');

    cy.get('#customer-email').clear({force: true});
    cy.get('[name="firstname"]').clear({force: true});
    cy.get('[name="lastname"]').clear({force: true});
    cy.get('[name="street[0]"]').clear({force: true});
    cy.get('[name="city"]').clear({force: true});
    cy.get('[name="postcode"]').clear({force: true});
    cy.get('[name="telephone"]').clear({force: true});


    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.wait('@getEstimateShippingMethods', {"timeout": 45000});

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#customer-email').type(customer.email);
        cy.wait('@isEmailAvailable', {"timeout": 35000});

        cy.get('[name="firstname"]').type(customer.firstName);
        cy.get('[name="lastname"]').type(customer.lastName);
        cy.get('[name="street[0]"]').type(customer.streetAddress);
        cy.get('[name="city"]').type(customer.city);
        cy.get('[name="postcode"]').type(customer.zipCode);
        cy.get('[name="country_id"]').select(customer.country, {force: true});
        cy.get('[name="telephone"]').type(customer.phone);
    });

    cy.wait('@getEstimateShippingMethods', {"timeout": 45000});
    cy.get('#s_method_flatrate').should('be.checked');
    cy.get('button.continue').click();
});

Cypress.Commands.add("checkOrderSuccess", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/onepage/success');
});

Cypress.Commands.add("checkOrderCancelled", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/onepage/failure');
});

Cypress.Commands.add("checkUnsupportedPayment", () => {
    cy.checkHostedFieldsError("This credit card type is not allowed for this payment method.");
});

Cypress.Commands.add("checkHostedFieldsError", (msg) => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/');
    cy.get('div[data-ui-id="checkout-cart-validationmessages-message-error"]', {timeout: 50000}).contains(
        msg
    );
});

Cypress.Commands.add("saveLastOrderId", () => {

    cy.get('body').then(($body) => {
        if ($body.find('.checkout-success > :nth-child(1) > span').length) {

            cy.get('.checkout-success > :nth-child(1) > span').then(($data) => {
                cy.fixture('order').then((order) => {
                    order.lastOrderId = $data.text();
                    cy.writeFile('cypress/fixtures/order.json', order);
                });
            });
        }
    });
});

/**
 * Process an order ( Checkout and pay with Hosted Fields)
 */
Cypress.Commands.add("processAnOrder", () => {
    cy.configureAndActivateHostedFields();
    cy.goToFront();
    cy.selectItemAndGoToCart();
    cy.goToCheckout();
    cy.fillShippingForm("FR");
    cy.get('#hipay_hosted_fields').click();

    cy.get('#hipay-field-cardHolder> iframe');
    cy.wait(3000);
    cy.fill_hostedfield_card("visa_ok");
    cy.get(".payment-method._active > .payment-method-content .actions-toolbar:visible button").click();
    cy.checkOrderSuccess();
    cy.saveLastOrderId();
});

/**
 * Process an order ( Checkout and pay with Hosted Fields)
 */
Cypress.Commands.add("processAnOrderWithBasket", () => {
    cy.activateOptionSendCart();
    cy.processAnOrder();
});
