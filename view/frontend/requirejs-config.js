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

var config = {
    map: {
        '*': {
            transparent: 'Magento_Payment/transparent',
            hipay_tpp: 'HiPay_FullserviceMagento/js/hipay-tpp',
            reqwest: 'HiPay_FullserviceMagento/js/reqwest',
            card: 'HiPay_FullserviceMagento/card',
            validation: 'HiPay_FullserviceMagento/js/validation'
        }
    },
    config: {
        mixins: {
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'HiPay_FullserviceMagento/js/grand-total-mixin': true
            }
        }
    }
};
