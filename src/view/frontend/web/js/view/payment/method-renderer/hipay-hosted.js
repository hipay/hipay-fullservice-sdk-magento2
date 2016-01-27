/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Hipay_FSMagento/payment/hipay-hosted'
            },
	
	        getCode: function() {
	            return 'hipay_hosted';
	        },
            isActive: function() {
                return true;
            }
        });
    }
);

