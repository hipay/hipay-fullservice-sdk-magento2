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
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted', //@override hipay-hosted
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
                redirectAfterPlaceOrder: false
            },
	        getCode: function() {
	            return 'hipay_sisal';
	        },
            isActive: function() {
                return true;
            }
        });
    }
);

