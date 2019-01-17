/**
 * Functionality tested
 *  - Basket is sent without mapping (Default value)
 *
 */
const fetchInput = require('../../../node_modules/@hipay/hipay-cypress-utils/utils/fetch-input.js');
describe('Process an Order with basket and without delivery mapping', function () {

    /**
     * Before Each
     */
    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('notification').as("notification");
        cy.fixture('basket').as("basket");
    });

    /**
     * Process an complete order (Configuration
     */
    it('Succeed an Order with basket', function () {
        cy.processAnOrderWithBasket();
    });

    /**
     * Check basket
     */
    it('Check Basket', function () {
        cy.connectAndSelectAccountOnHipayBO();
        cy.openTransactionOnHipayBO(this.order.lastOrderId);
        cy.openNotificationOnHipayBO(116).then(() => {
            var basketTransaction = fetchInput("basket",decodeURI(this.data));
            assert.equal(basketTransaction,JSON.stringify(this.basket.basketWithoutMapping));
        });
    });
});



