/**
 * Functionality tested
 *  - Signature verification
 *  - Notification system and return
 *  - Status order
 *  - Comments
 *  - Transaction page
 *
 */
describe('Pay by credit card and process notification', function () {



    /**
     * Process an complete order (Configuration
     */
    it('Succeed an Order ', function () {    /**
     * Before Each
     */
    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('notification').as("notification");
    });
        cy.processAnOrder();
    });

    /**
     * Send transaction for authorization ( Bad signature )
     */
    it('Process Notification Authorization with bad signature', function () {
        cy.connectAndSelectAccountOnHipayBO();
        cy.openTransactionOnHipayBO(this.order.lastOrderId);
        cy.openNotificationOnHipayBO(116).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: "BAD HASH"},true);
        });
    });

    /**
     * Send transaction for authorization ( Good signature )
     */
    it('Process Notification Authorization with good signature', function () {
        cy.processAnNotification(this.notification.url,this.order.lastOrderId, 116);
    });

    /**
     * Check order status
     */
    it('Check order status and comments', function () {
        cy.logToAdmin();
        cy.get('#menu-magento-sales-sales > [onclick="return false;"]').click();
        cy.get('.item-sales-order > a').click();
        cy.goToDetailOrder(this.order.lastOrderId);
        cy.get('#order_status').contains('Authorized');
        cy.get('ul.note-list :nth-child(1) > .note-list-comment').contains("Notification \"completed\" Authorized amount of");
    });

    /**
     *  Process Notification Capture
     */
    it('Process Notification 118', function () {
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId);
        cy.openNotificationOnHipayBO(118).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash},false);
        });
    });

    /**
     * Check order status and comments after notification 118
     */
    it('Check order status and comments', function () {
        cy.logToAdmin();
        cy.get('#menu-magento-sales-sales > [onclick="return false;"]').click();
        cy.get('.item-sales-order > a').click();
        cy.goToDetailOrder(this.order.lastOrderId);
        cy.get('#order_status').contains('Processing');
        cy.get('ul.note-list :nth-child(1) > .note-list-comment').contains("Notification \"completed\" Registered notification about captured amount of €64.00");
        cy.get('#sales_order_view_tabs_order_transactions_content').click({force: true});
        cy.get("#order_transactions_table tr").should('have.length', 2);
    });

});



