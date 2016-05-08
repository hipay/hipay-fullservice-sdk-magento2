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
     	'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc'
    ],
    function (Component) {

        'use strict';
        return Component.extend({
            
        	defaults: {
        		template: 'HiPay_FullserviceMagento/payment/hipay-cc-split',
        	},
            /**
             * @override
             */
            initObservable: function () {
            	
                this._super();
                return this;
            },
        	/**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
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
            /**
             * @override
             */
            getCode: function () {
                return 'hipay_ccsplit';
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
            }
            
        });
    }
);

