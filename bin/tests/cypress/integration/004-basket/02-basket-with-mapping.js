/**
 * Functionality tested
 *  - Mapping delivery method
 *  - Mapping category
 */
const fetchInput = require('../../../node_modules/@hipay/hipay-cypress-utils/utils/fetch-input.js');
describe('Pay by credit card and process notification', function () {

    /**
     * Before Each
     */
    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('notification').as("notification");
        cy.fixture('basket').as("basket");
    });

    /**
     * Process mapping for all category
     */
    it('HiPay mapping Categories', function () {
        cy.logToAdmin();
        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu').click();
        cy.get('.item-hipay-mapping-categories > a > span').click({force: true});
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Collections");
        cy.get('#cart_categories_category_hipay_id').select("Home & Gardening");
        cy.get('#save').click();
        cy.get('.message > div').contains("Your settings have been saved.");
        cy.get('.item-hipay-mapping-categories > a > span').click({force: true});
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Gear");
        cy.get('#cart_categories_category_hipay_id').select("Home appliances");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Men");
        cy.get('#cart_categories_category_hipay_id').select("Home & Gardening");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Promotions");
        cy.get('#cart_categories_category_hipay_id').select("Home appliances");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Sale");
        cy.get('#cart_categories_category_hipay_id').select("Home & Gardening");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Training");
        cy.get('#cart_categories_category_hipay_id').select("Home appliances");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_categories_category_magento_id').select("Training");
        cy.get('#cart_categories_category_hipay_id').select("Home appliances");
        cy.get('#save').click();
        cy.get('.message > div').contains("You have already done this mapping.");
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(2)').contains('Collections');
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(3)').contains('Home & Gardening');
    });

    /**
     * Process mapping for delivery mapping
     */
    it('HiPay mapping delivery mapping', function () {
        cy.logToAdmin();
        cy.get('#menu-hipay-fullservicemagento-hipay-payment-menu').click();
        cy.get('.item-hipay-mapping-shipping > a > span').click({force: true});
        cy.get('#add').click();
        cy.get('#cart_mappingshipping_magento_shipping_code').select("flatrate_flatrate");
        cy.get('#cart_mappingshipping_hipay_shipping_id').select("STORE - EXPRESS");
        cy.get('#cart_mappingshipping_delay_preparation').type("1");
        cy.get('#cart_mappingshipping_delay_delivery').type("5");
        cy.get('#save').click();
        cy.get('#add').click();
        cy.get('#cart_mappingshipping_magento_shipping_code').select("tablerate_bestway");
        cy.get('#cart_mappingshipping_hipay_shipping_id').select("ELECTRONIC - INSTANT");
        cy.get('#cart_mappingshipping_delay_preparation').type("10");
        cy.get('#cart_mappingshipping_delay_delivery').type("1");
        cy.get('#save').click();
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(2)').contains('flatrate - Fixed');
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(3)').contains('STORE - EXPRESS');
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(4)').contains('1');
        cy.get('table.data-grid-draggable tbody tr:nth-child(1) td:nth-child(5)').contains('5');
    });

    /**
     * Process an complete order
     */
    it('Succeed an Order with basket', function () {
        cy.processAnOrderWithBasket();
    });

    /**
     * Check basket
     */
    it('Check Basket and delivery method', function () {
        cy.connectAndSelectAccountOnHipayBO();
        cy.openTransactionOnHipayBO(this.order.lastOrderId);
        cy.openNotificationOnHipayBO(116).then(() => {
            var basketTransaction = fetchInput("basket",decodeURI(this.data));
            // At the moment, category and delivery method are not sent in notification, so no change in basket ...
            assert.equal(basketTransaction,JSON.stringify(this.basket.basketWithoutMapping));
        });
    });

});



