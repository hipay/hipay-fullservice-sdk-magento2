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
        		allowOneclick: window.checkoutConfig.payment.hiPayFullservice.useOneclick,
        		selectedCard:  window.checkoutConfig.payment.hiPayFullservice.selectedCard,
        		customerCards: window.checkoutConfig.payment.hiPayFullservice.customerCards,
        		createOneclick: false,
        		creditCardType: '',
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
                        'creditCardToken'
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
            	
                if(this.useOneclick()){
            		this.creditCardToken(this.selectedCard());
            	}
            	
            	//Set selected card token
                this.selectedCard.subscribe(function(value) {
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
                        'eci': this.eci,
                        'cc_type': this.creditCardType()
                    }
                };
            },
        });
    }
);

