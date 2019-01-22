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
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'hipay_hosted',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted'
            },
            {
                type: 'hipay_hosted_fields',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted-fields'
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
                type: 'hipay_sisal',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sisal'
            },
            {
                type: 'hipay_dexia',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-dexia'
            },
            {
                type: 'hipay_sdd',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sdd'
            },
            {
                type: 'hipay_yandexapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-yandexapi'
            },
            {
                type: 'hipay_webmoneyapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-webmoneyapi'
            },
            {
                type: 'hipay_postfinancecardapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-postfinancecardapi'
            },
            {
                type: 'hipay_sofortapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sofortapi'
            },
            {
                type: 'hipay_ing',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-ing'
            },
            {
                type: 'hipay_ideal',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-ideal'
            }
            , {
                type: 'hipay_giropay',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-giropay'
            },
            {
                type: 'hipay_postfinanceefinanceapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-postfinanceefinanceapi'
            },
            {
                type: 'hipay_przelewy24api',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-przelewy24'
            },
            {
                type: 'hipay_paypalapi',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-paypalapi'
            },
            {
                type: 'hipay_aura',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-aura'
            },
            {
                type: 'hipay_aura',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-aura'
            },
            {
                type: 'hipay_banamex',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-banamex'
            },
            {
                type: 'hipay_banco',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-banco'
            },
            {
                type: 'hipay_caixa',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-caixa'
            },
            {
                type: 'hipay_bbva',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bbva'
            },
            {
                type: 'hipay_boleto',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-boleto'
            },
            {
                type: 'hipay_bradesco',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bradesco'
            },
            {
                type: 'hipay_itau',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-itau'
            },
            {
                type: 'hipay_oxxo',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-oxxo'
            },
            {
                type: 'hipay_santander',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-santander'
            },
            {
                type: 'hipay_santandercash',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-santandercash'
            },
            {
                type: 'hipay_facilypay3X',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-facilypay3X'
            },
            {
                type: 'hipay_facilypay4X',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-facilypay4X'
            },
            {
                type: 'hipay_klarna',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-klarna'
            },
            {
                type: 'hipay_bnpp4X',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bnpp4X'
            },
            {
                type: 'hipay_bnpp3X',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bnpp3X'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
