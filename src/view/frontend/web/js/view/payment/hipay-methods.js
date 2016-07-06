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
                type: 'hipay_hostedsplit',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted-split'
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
