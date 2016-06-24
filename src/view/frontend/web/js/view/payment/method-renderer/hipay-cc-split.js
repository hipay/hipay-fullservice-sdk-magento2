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
     	'Magento_Checkout/js/model/totals',
     	'mage/storage'
    ],
    function ($,ko,Component,totals,storage) {
        'use strict';

        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc-split',
        		selectedPaymentProfile: '',
        		splitAmounts: [],
        		refreshConfigUrl: window.checkoutConfig.payment.hipaySplit.refreshConfigUrl 
        	},
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
                       'splitAmounts',
                   ]);

              // this.reloadPaymentProfiles();
               
                totals.totals.subscribe(function(newValue){
                	
                	//Ajax call to update splitAmounts         		
                	self.reloadPaymentProfiles();
                	
                });

              //Set expiration year to credit card data object
                this.selectedPaymentProfile.subscribe(function(value) {
                	if(value){
                		self.splitAmounts(self.getSplitAmountByProfile(value));
                	}
                	else{
                		self.splitAmounts([]);
                	}
                });
                
                if(this.hasPaymentProfiles()){
                	//this.splitAmounts = ko.observableArray(this.getSplitAmountByProfile(this.getFirstPaymentProfileId()));
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
            	storage.get(
                		
                        this.refreshConfigUrl
                    ).done(
                        function (response) {
                        	if(response.payment){
                        		console.log(response);
                        		
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
            	

            },
            updateSplitAmounts: function(payment){
            	//Merge with current checkoutConfig
        		$.extend(true,window.checkoutConfig.payment,payment);
        	
            	//Reload split amounts
        		/*if(this.hasPaymentProfiles()){
                	this.selectedPaymentProfile(this.getFirstPaymentProfileId());
                }*/
        		//console.log('New Splitamounts');
        		//console.log(this.splitAmounts());
            	this.splitAmounts(this.getSplitAmountByProfile(this.selectedPaymentProfile()));
            	
            	//Force refresh binding
            	this.splitAmounts.valueHasMutated();
            	console.log(this.splitAmounts());
            	
            },
            getSplitAmounts: function (){
            	return this.splitAmounts();
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

