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
  'uiComponent',
  'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
  'use strict';
  rendererList.push(
    {
      type: 'hipay_hosted',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted'
    },
    {
      type: 'hipay_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted-fields'
    },
    {
      type: 'hipay_applepay',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-applepay'
    },
    {
      type: 'hipay_sisal_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sisal-hosted-fields'
    },
    {
      type: 'hipay_sisal',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sisal'
    },
    {
      type: 'hipay_sdd',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sdd'
    },
    {
      type: 'hipay_postfinancecardapi',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-postfinancecardapi'
    },
    {
      type: 'hipay_sofortapi',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sofortapi'
    },
    {
      type: 'hipay_bancontact_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bancontact-hosted-fields'
    },
    {
      type: 'hipay_ideal_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-ideal-hosted-fields'
    },
    {
      type: 'hipay_ideal',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-ideal'
    },
    {
      type: 'hipay_giropay',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-giropay'
    },
    {
      type: 'hipay_postfinanceefinanceapi',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-postfinanceefinanceapi'
    },
    {
      type: 'hipay_przelewy24api',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-przelewy24'
    },
    {
      type: 'hipay_paypalapi',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-paypalapi'
    },
    {
      type: 'hipay_mbway_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-mbway-hosted-fields'
    },
    {
      type: 'hipay_mbway',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-mbway'
    },
    {
      type: 'hipay_alma3X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-alma3X'
    },
    {
      type: 'hipay_alma4X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-alma4X'
    },
    {
      type: 'hipay_facilypay3X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-facilypay3X'
    },
    {
      type: 'hipay_facilypay4X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-facilypay4X'
    },
    {
      type: 'hipay_creditlong',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-creditlong'
    },
    {
      type: 'hipay_creditlong_opc2',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-creditlong_opc2'
    },
    {
      type: 'hipay_creditlong_opc3',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-creditlong_opc3'
    },
    {
      type: 'hipay_creditlong_opc4',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-creditlong_opc4'
    },
    {
      type: 'hipay_klarna',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-klarna'
    },
    {
      type: 'hipay_bnpp4X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bnpp4X'
    },
    {
      type: 'hipay_bnpp3X',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-bnpp3X'
    },
    {
      type: 'hipay_mybank',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-mybank'
    },
    {
      type: 'hipay_multibanco_hosted_fields',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-multibanco-hosted-fields'
    },
    {
      type: 'hipay_multibanco',
      component:
        'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-multibanco'
    }
  );
  /**
   * Add view logic here if needed
   */
  return Component.extend({});
});
