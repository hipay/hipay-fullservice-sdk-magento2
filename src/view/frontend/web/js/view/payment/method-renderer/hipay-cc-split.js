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
     	'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc'
    ],
    function ($,Component) {

        'use strict';
        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc-split',
        		selectedPaymentProfile: '',
        		splitAmounts: []
        	},
            /**
             * @override
             */
            initObservable: function () {
            	var self = this;
            	
                this._super().
                observe([
                       'selectedPaymentProfile',
                       'splitAmounts'
                   ]);
                
              //Set expiration year to credit card data object
                this.selectedPaymentProfile.subscribe(function(value) {
                    self.splitAmounts(value.splitAmounts);
                });
                
                if(this.hasPaymentProfiles()){
                	this.selectedPaymentProfile(this.getFirstPaymentProfile());
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
            getPaymentProfiles(){
            	console.log(window.checkoutConfig.payment.hipaySplit.paymentProfiles[this.getCode()]);
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
            }
            
        });
    }
);

