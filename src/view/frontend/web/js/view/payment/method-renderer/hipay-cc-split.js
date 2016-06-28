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
     	'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc',
     	'Magento_Checkout/js/model/totals'
    ],
    function ($,ko,Component,totals) {
        'use strict';
        var splitAmounts = ko.observableArray();
        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc-split',
        		selectedPaymentProfile: '',
        		splitAmounts: splitAmounts,
        		refreshConfigUrl: window.checkoutConfig.payment.hipaySplit.refreshConfigUrl,
        	},
        	isLoading: ko.observable(false),
        	initialize: function(){
        		this._super();
        	},
            /**
             * @override
             */
            initObservable: function () {
            	var self = this;
            	
                this._super().
                observe([
                       'selectedPaymentProfile',
                   ]);
               
                totals.totals.subscribe(function(newValue){
                	
                	//Ajax call to update splitAmounts         		
                	self.reloadPaymentProfiles();
                	
                });

              //Set expiration year to credit card data object
                this.selectedPaymentProfile.subscribe(function(value) {
                	self.splitAmounts.removeAll()
                	if(value){
                		
                		$.each(self.getSplitAmountByProfile(value), function(index,split){
                			self.splitAmounts.push(split);
                		});
                	}
                });
                
                if(this.hasPaymentProfiles()){
                	this.selectedPaymentProfile(this.getFirstPaymentProfileId());
                }
                
                return this;
            },
        	/**
             * @returns {Boolean}
             */
            isActive: function () {
                return this.hasPaymentProfiles();
            },
            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },
            context: function() {
                return this;
            },
            getData: function(){
            	
            	var parent = this._super();           
            	var additionalData = {
            			'additional_data':{            				
            				'profile_id': this.selectedPaymentProfile()
            			}
            	}

            	return $.extend(true, parent, additionalData);
            },
            /**
             * @override
             */
            getCode: function () {
                return 'hipay_ccsplit';
            },
            reloadPaymentProfiles: function(){
            	var self = this;
            	this.isLoading(true);
            	$.ajax({
                    url: this.refreshConfigUrl,
                    type: 'GET',
                    global: true,
                    contentType: 'application/json',
                    showLoader: true
                }).done(
                        function (response) {
                        	if(response.payment){
	                        	self.updateSplitAmounts(response.payment);                   	
                        	}
                        	else{
                        		console.log(response);
                                
                        	}
                        	
                        }
                    ).fail(
                        function (response) {
                        	console.log(response);
                        }
                    );
            	this.isLoading(false);
            },
            updateSplitAmounts: function(payment){
            	var self = this;
            	//Merge with current checkoutConfig
        		$.extend(true,window.checkoutConfig.payment,payment);
        		
        		this.splitAmounts.removeAll();
        		
        		$.each(this.getSplitAmountByProfile(this.selectedPaymentProfile()), function(index,split){
        			self.splitAmounts.push(split);
        		});

            },
            getSplitAmounts: function (){
            	return this.splitAmounts;
            },
            getPaymentProfiles: function(){
            	return window.checkoutConfig.payment.hipaySplit.paymentProfiles[this.getCode()];
            },
            hasPaymentProfiles: function(){
            	return this.getPaymentProfiles().length > 0;
            },
            getFirstPaymentProfile: function(){
            	var pp= this.getPaymentProfiles();
            	for(var i=0;i<pp.length;i++){
            		return pp[i];
            	}
            },
            getFirstPaymentProfileId: function(){
            	return this.getFirstPaymentProfile().profileId;
            },
            getSplitAmountByProfile: function(profileId){
            	var ppArr = this.getPaymentProfiles();
            	for(var i=0;i<ppArr.length;i++){
            		if(ppArr[i].profileId == profileId){
            			return ppArr[i].splitAmounts;
            		}
            	}
            	
            	return [];
            }
            
        });
    }
);

