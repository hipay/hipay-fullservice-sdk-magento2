/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
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
        		allowOneclick: window.checkoutConfig.payment.hiPayFullservice.useOneclick,
        		selectedCard:  window.checkoutConfig.payment.hiPayFullservice.selectedCard,
        		customerCards: window.checkoutConfig.payment.hiPayFullservice.customerCards,
        		createOneclick: false,
        		creditCardType: '',
        		defaultEci: window.checkoutConfig.payment.hiPayFullservice.defaultEci,
        		recurringEci: window.checkoutConfig.payment.hiPayFullservice.recurringEci,
        		eci: window.checkoutConfig.payment.hiPayFullservice.defaultEci,
        		showForm: true
        	},
        	getAfterPlaceOrderUrl: function(){
	        	return this.afterPlaceOrderUrl[this.getCode()];
	        },
            initObservable: function () {
            	var self = this;
                this._super()
                    .observe([
                        'selectedCard',
                        'createOneclick',
                        'creditCardType',
                        'creditCardToken',
                        'eci'
                    ]);
                
                this.showForm = ko.computed(function () {

                    return !(self.useOneclick() && self.customerHasCard()) ||
                    		self.selectedCard() === undefined ||
                    		self.selectedCard() === '';
                }, this);
                
                return this;
            },
            initialize: function(){
            	var self = this;
            	this._super();
            	
            	if(this.selectedCard() && this.useOneclick()){
            		this.eci(this.recurringEci);
            		this.creditCardToken(this.selectedCard());
            	}
            	
            	//Set selected card token
                this.selectedCard.subscribe(function(value) {
                	if(value){
                		self.eci(self.recurringEci);           
                	}
                	else{
                		self.eci(self.defaultEci);
                	}

                	self.creditCardToken(value);
                	self.creditCardType(self.getCustomerCardByToken(value).ccType);
                });
            	
            },
            /**
             * @returns Array
             */
            getCustomerCards: function(){
            	return this.customerCards;
            },
            getCustomerCardByToken: function(token){
            	 for (var i = 0; i < this.customerCards.length; i++) {
            		 if(this.customerCards[i].ccToken == token){
            			 return this.customerCards[i];
            		 }
            	 }
            	 return {};
            },
            useOneclick: function(){
            	return this.allowOneclick[this.getCode()];
            },
            
            customerHasCard: function(){
            	return this.getCustomerCards().length > 0;
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'create_oneclick': this.createOneclick(),
                        'card_token': this.creditCardToken(),
                        'eci': this.eci(),
                        'cc_type': this.creditCardType()
                    }
                };
            },
        });
    }
);

