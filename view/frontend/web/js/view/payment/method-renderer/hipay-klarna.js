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
    'jquery',
    'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted'
], function ($, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'HiPay_FullserviceMagento/payment/hipay-hosted',
            redirectAfterPlaceOrder: false
        },

        getCode: function () {
            return 'hipay_klarna';
        },
        isActive: function () {
            return true;
        }
    });
});
