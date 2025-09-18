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
define([
    'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-oney'
], function (OneyAbstract) {
    'use strict';
    return OneyAbstract.extend({
        defaults: {
            afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl.hipay_facilypay4X,
            env: window.checkoutConfig.payment.hipay_facilypay4X
                ? window.checkoutConfig.payment.hipay_facilypay4X.env
                : 'stage',
            apiUsernameTokenJs: window.checkoutConfig.payment.hipay_facilypay4X
                ? window.checkoutConfig.payment.hipay_facilypay4X.apiUsernameTokenJs
                : '',
            apiPasswordTokenJs: window.checkoutConfig.payment.hipay_facilypay4X
                ? window.checkoutConfig.payment.hipay_facilypay4X.apiPasswordTokenJs
                : '',
            paymentProductFees: window.checkoutConfig.payment.hipay_facilypay4X
                ? window.checkoutConfig.payment.hipay_facilypay4X.paymentProductFees
                : '',
            locale: window.checkoutConfig.payment.hiPayFullservice.locale
                ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_facilypay4X
                : 'fr_FR'
        },

        getProductCode: function () {
            return '4xcb';
        },

        getCode: function () {
            return 'hipay_facilypay4X';
        }
    });
});
