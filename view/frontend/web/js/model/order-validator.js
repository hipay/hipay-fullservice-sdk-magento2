/**
 * HiPay Fullservice Magento - Order Validator
 * 
 * Generic order validation for all payment methods (PayPal, Apple Pay, Google Pay, etc.)
 * Validates billing address, shipping address, payment method, and shipping method
 *
 * @author    HiPay
 * @copyright Copyright (c) 2024 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator'
], function (quote, stepNavigator) {
    'use strict';
    
    function canPlaceOrder() {
        var issues = [];
        
        // Check billing address
        if (!quote.billingAddress() || !quote.billingAddress().firstname) {
            issues.push('Billing address is incomplete');
        }
        
        // Check shipping address (for non-virtual products)
        if (!quote.isVirtual() && (!quote.shippingAddress() || !quote.shippingAddress().firstname)) {
            issues.push('Shipping address is required');
        }
        
        // Check payment method
        if (!quote.paymentMethod()) {
            issues.push('Payment method not selected');
        }
        
        // Check shipping method (for non-virtual products)
        if (!quote.isVirtual() && !quote.shippingMethod()) {
            issues.push('Shipping method not selected');
        }
        
        return {
            canPlace: issues.length === 0,
            issues: issues
        };
    }
    
    return {
        canPlaceOrder: canPlaceOrder
    };
});
