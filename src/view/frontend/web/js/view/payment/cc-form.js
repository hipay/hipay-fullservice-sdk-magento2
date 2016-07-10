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
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'HiPay_FullserviceMagento/js/view/payment',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate'
    ],
    function ($, Component, creditCardData, cardNumberValidator, $t) {
        return Component.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: '',
                selectedCardType: null,
                creditCardOwner: ''
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType',
                        'creditCardOwner'
                    ]);
                return this;
            },

            initialize: function() {
                var self = this;
                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function(value) {
                    var result;
                    self.selectedCardType(null);

                    if (value == '' || value == null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                    }
                });

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function(value) {
                    creditCardData.cvvCode = value;
                });
            },

            getCode: function() {
                return 'cc';
            },
            getData: function() {
            	
            	var parent = this._super();           
            	var additionalData = {          				
            				'cc_cid': this.creditCardVerificationNumber(),
                            'cc_ss_start_month': this.creditCardSsStartMonth(),
                            'cc_ss_start_year': this.creditCardSsStartYear(),
                            'cc_type': this.creditCardType(),
                            'cc_exp_year': this.creditCardExpYear(),
                            'cc_exp_month': this.creditCardExpMonth(),
                            'cc_number': this.creditCardNumber(),
                            'cc_owner': this.creditCardOwner()
            	}
            	return $.extend(true, parent, additionalData);

            },
            getDisplayCardOwner: function(){
            	return window.checkoutConfig.payment.hiPayFullservice.displayCardOwner[this.getCode()];
            },
            getCcAvailableTypes: function() {
                return window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
            },
            getIcons: function (type) {
                return window.checkoutConfig.payment.hipayCc.icons.hasOwnProperty(type)
                    ? window.checkoutConfig.payment.hipayCc.icons[type]
                    : false
            },
            getCcMonths: function() {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
            },
            getCcYears: function() {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },
            hasVerification: function() {
                return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
            },
            hasSsCardType: function() {
                return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
            },
            getCvvImageUrl: function() {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
            },
            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            },
            getSsStartYears: function() {
                return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
            },
            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            getSsStartYearsValues: function() {
                return _.map(this.getSsStartYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function() {
                return false;
            },
            getCcTypeTitleByCode: function(code) {
                var title = '';
                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function(number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function() {
                return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            }
        });
    }
);
