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
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
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
        return Component.extend(
            {
                defaults: {
                    creditCardType: '',
                    creditCardExpYear: '',
                    creditCardExpMonth: '',
                    creditCardNumber: '',
                    creditCardSsStartMonth: '',
                    creditCardSsStartYear: '',
                    creditCardVerificationNumber: '',
                    selectedCardType: null,
                    creditCardOwner: '',
                    showCVV: true,
                    hipaySdk: null,
                    locale: "",
                    browserInfo: {}
                },

                initObservable: function () {
                    this._super()
                    .observe(
                        [
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType',
                        'creditCardOwner',
                        'showCVV',
                        'domReady!',
                        'browserInfo'
                        ]
                    );
                    return this;
                },

                initHiPayConfiguration: function (initCallback) {
                    var self = this;
                    var lang = 'en';

                    if (self.locale
                        && self.locale.length > 2
                    ) {
                        lang = self.locale.substr(0, 2);
                    }

                    self.hipaySdk = HiPay(
                        {
                            username: self.apiUsernameTokenJs,
                            password: self.apiPasswordTokenJs,
                            environment: self.env,
                            lang: lang
                        }
                    );

                    if (initCallback) {
                        initCallback(self);
                    }

                    if (self.hipaySdk.getDeviceFingerprint() === undefined) {
                        let retryCounter = 0;
                        let interval = setInterval(
                        function timeoutFunc()
                            {
                            retryCounter++;
                            // If global_info init send event
                            if (self.hipaySdk.getDeviceFingerprint() !== undefined) {
                                $("#ioBBCard").val(self.hipaySdk.getDeviceFingerprint());
                                clearInterval(interval);
                            }
                            // Max retry = 3
                            if (retryCounter > 3) {
                                clearInterval(interval);
                            }
                        },
                            1000
                        );
                    }
                },

                initialize: function () {
                    var self = this;
                    this._super();

                    this.initHiPayConfiguration();

                    //Set credit card number to credit card data object
                    this.creditCardNumber.subscribe(
                        function (value) {
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
                                self.showCVV(self.isCreditCardTypeNeedCVV(value));
                                self.selectedCardType(result.card.type);
                                creditCardData.creditCard = result.card;
                            }

                            if (result.isValid) {
                                creditCardData.creditCardNumber = value;
                                self.creditCardType(result.card.type);
                            }
                        }
                    );

                    //Set expiration year to credit card data object
                    this.creditCardExpYear.subscribe(
                        function (value) {
                            creditCardData.expirationYear = value;
                        }
                    );

                    //Set expiration month to credit card data object
                    this.creditCardExpMonth.subscribe(
                        function (value) {
                            creditCardData.expirationYear = value;
                        }
                    );

                    //Set cvv code to credit card data object
                    this.creditCardVerificationNumber.subscribe(
                        function (value) {
                            creditCardData.cvvCode = value;
                        }
                    );
                },

                isCreditCardTypeNeedCVV: function (cardNumber) {
                    return !(new RegExp('^(6703)[0-9]{8,15}$').test(cardNumber));
                },

                getCode: function () {
                    return 'cc';
                },
                getData: function () {

                    var parent = this._super();
                    var additionalData = {
                        'additional_data': {
                            'cc_cid': this.creditCardVerificationNumber(),
                            'cc_ss_start_month': this.creditCardSsStartMonth(),
                            'cc_ss_start_year': this.creditCardSsStartYear(),
                            'cc_type': this.creditCardType(),
                            'cc_exp_year': this.creditCardExpYear(),
                            'cc_exp_month': this.creditCardExpMonth(),
                            'cc_number': this.creditCardNumber(),
                            'cc_owner': this.creditCardOwner(),
                            'browser_info': JSON.stringify(this.hipaySdk.getBrowserInfo())
                        }
                    };

                    return $.extend(true, parent, additionalData);
                },
                getDisplayCardOwner: function () {
                    return window.checkoutConfig.payment.hiPayFullservice.displayCardOwner[this.getCode()];
                },
                getCcAvailableTypes: function () {
                    return window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
                },
                getCcMonths: function () {
                    return window.checkoutConfig.payment.ccform.months[this.getCode()];
                },
                getCcYears: function () {
                    return window.checkoutConfig.payment.ccform.years[this.getCode()];
                },
                hasVerification: function () {
                    return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
                },
                hasSsCardType: function () {
                    return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
                },
                getCvvImageUrl: function () {
                    return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
                },
                getCvvImageHtml: function () {
                    return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
                },
                getSsStartYears: function () {
                    return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
                },
                getCcAvailableTypesValues: function () {
                    return _.map(
                        this.getCcAvailableTypes(),
                        function (value, key) {
                            return {
                                'value': key,
                                'type': value
                            }
                        }
                    );
                },
                getCcMonthsValues: function () {
                    return _.map(
                        this.getCcMonths(),
                        function (value, key) {
                            return {
                                'value': key,
                                'month': value
                            }
                        }
                    );
                },
                getCcYearsValues: function () {
                    return _.map(
                        this.getCcYears(),
                        function (value, key) {
                            return {
                                'value': key,
                                'year': value
                            }
                        }
                    );
                },
                getSsStartYearsValues: function () {
                    return _.map(
                        this.getSsStartYears(),
                        function (value, key) {
                            return {
                                'value': key,
                                'year': value
                            }
                        }
                    );
                },

                /**
                 *  Get global fingerprint  on dom load of checkout
                 *
                 * @returns {*}
                 */
                getFingerprint: function () {
                    if ($('#ioBB')) {
                        return $('#ioBB').val();
                    } else {
                        return '';
                    }
                },

                isShowLegend: function () {
                    return false;
                },
                getCcTypeTitleByCode: function (code) {
                    var title = '';
                    _.each(
                        this.getCcAvailableTypesValues(),
                        function (value) {
                            if (value['value'] == code) {
                                title = value['type'];
                            }
                        }
                    );
                    return title;
                },
                formatDisplayCcNumber: function (number) {
                    return 'xxxx-' + number.substr(-4);
                },
                getInfo: function () {
                    return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                    ];
                }
            }
        );
    }
);
