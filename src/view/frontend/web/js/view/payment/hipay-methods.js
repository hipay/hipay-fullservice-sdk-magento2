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
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'hipay_hosted',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted'
            },
            {
                type: 'hipay_cc',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc'
            },
            {
                type: 'hipay_ccsplit',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc-split'
            },
            {
            	// New local method with hosted template
                type: 'hipay_sisal',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sisal'
            }
            ,
            {
                type: 'hipay_qiwiwallet',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-qiwiwallet'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
