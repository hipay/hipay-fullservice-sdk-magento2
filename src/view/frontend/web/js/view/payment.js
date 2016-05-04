/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

define(
    [
     	'jquery',
     	'ko',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, ko, Component) {
        'use strict';
        return Component.extend({
        	
        	defaults: {
        		creditCardToken: null,
        		redirectAfterPlaceOrder: false,
        		afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
        		useOneclick: window.checkoutConfig.payment.hiPayFullservice.useOneclick[this.getCode()],
        		selectedCard: {}
        	},
        	getAfterPlaceOrderUrl: function(){
	        	return this.afterPlaceOrderUrl[this.getCode()];
	        },
            initObservable: function () {
                this._super()
                    .observe([
                        'selectedCard',
                    ]);
                return this;
            },
            /**
             * @returns Array
             */
            getCustomerCards: function(){
            	return [];
            },
            useOneclick: function(){
            	return this.useOneclick;
            },
            
            customerHasCard: function(){
            	return this.getCustomerCards().length > 0;
            }	
        });
    }
);

