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
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */

define(
    [
     	'jquery',
     	'ko',
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted',
        'mage/storage'
    ],
    function ($, ko, Component,storage) {
        'use strict';
        var splitAmounts = ko.observableArray();
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-hosted-split',
                selectedPaymentProfile: '',
        		splitAmounts: splitAmounts,
        		refreshConfigUrl: window.checkoutConfig.payment.hipaySplit.refreshConfigUrl
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

              //Ajax call to update splitAmounts, when method view is loaded.        
            	self.reloadPaymentProfiles();

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
	        context: function() {
                return this;
            },
	        getCode: function() {
	            return 'hipay_hostedsplit';
	        },
            isActive: function() {
                return this.hasPaymentProfiles();
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
            reloadPaymentProfiles: function(){
            	var self = this;
            	storage.get(
                		
                        this.refreshConfigUrl
                    ).done(
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
            getPaymentProfiles(){
            	return window.checkoutConfig.payment.hipaySplit.paymentProfiles[this.getCode()];
            },
            hasPaymentProfiles(){
            	return this.getPaymentProfiles().length > 0;
            },
            getFirstPaymentProfile(){
            	var pp= this.getPaymentProfiles();
            	for(var i=0;i<pp.length;i++){
            		return pp[i];
            	}
            },
            getFirstPaymentProfileId(){
            	return this.getFirstPaymentProfile().profileId;
            },
            getSplitAmountByProfile(profileId){
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

