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
     	'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
        	/**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-cc'
            }
            
        });
    }
);

