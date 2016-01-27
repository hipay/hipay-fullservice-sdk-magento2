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
                component: 'Hipay_Fullservice/js/view/payment/method-renderer/hipay-hosted'
            }/*,
            {
                type: 'hipay_api',
<<<<<<< HEAD
                component: 'Hipay_Fullservice/js/view/payment/method-renderer/hipay-api'
=======
                component: 'Hipay_FSMagento/js/view/payment/method-renderer/hipay-api'
>>>>>>> branch 'develop' of git@github.com:hipay/hipay-fullservice-sdk-magento2.git
            }*/
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
