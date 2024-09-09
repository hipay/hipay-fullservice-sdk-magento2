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
    'HiPay_FullserviceMagento/js/hipay-paypal-config',
    'domReady!'
], function (hipayPaypalConfig) {
    'use strict'
    var lastState = null,
        debounceTimer = null,
        isPayPalSelected = false

    function toggleFields(shouldEnable) {
        ['button_color', 'button_shape', 'button_label', 'button_height', 'bnpl'].forEach(
            function (fieldId) {
                var field = document.getElementById(
                        'payment_us_hipay_paypalapi_' + fieldId
                    ),
                    HostedPageField = document.getElementById(
                        'payment_us_hipay_hosted_paypal_' + fieldId
                    )
                if (field) field.disabled = !shouldEnable || !isPayPalSelected
                if (HostedPageField) HostedPageField.disabled = !shouldEnable || !isPayPalSelected
            }
        )

        var v2StatusRow = document.querySelector(
                '#row_payment_us_hipay_paypalapi_paypal_v2_status'
            ),
            v2StatusRowHostedPage = document.querySelector(
                '#row_payment_us_hipay_hosted_paypal_v2_status'
            )
        if (v2StatusRow) {
            v2StatusRow.style.display = (shouldEnable && isPayPalSelected) ? 'none' : 'table-row'
        }
        if (v2StatusRowHostedPage) {
            v2StatusRowHostedPage.style.display = (shouldEnable && isPayPalSelected) ? 'none' : 'table-row'
        }

        // Hide/show the PayPal hosted row
        var paypalHostedRow = document.getElementById('row_payment_us_hipay_hosted_paypal');
        if (paypalHostedRow) {
            paypalHostedRow.style.display = isPayPalSelected ? '' : 'none';
        }
    }

    function isHiPayPayPalSectionActive() {
        var sectionDiv = document.querySelector(
                '#row_payment_us_hipay_paypalapi .section-config'
            ),
            hostedSectionDiv = document.querySelector(
                '#row_payment_us_hipay_hosted_paypal .section-config'
            )
        return sectionDiv?.classList.contains('active') || hostedSectionDiv?.classList.contains('active')
    }

    function checkPayPalSelected() {
        var select = document.getElementById('payment_us_hipay_hosted_payment_products')
        if (select) {
            isPayPalSelected = Array.from(select.selectedOptions).some(option => option.value === 'paypal')
        } else {
            isPayPalSelected = false
        }
        toggleFields(lastState)
    }

    function handleHiPayPayPalSection() {
        var isActive = isHiPayPayPalSectionActive()
        if (isActive !== lastState) {
            lastState = isActive
            if (
                isActive &&
                typeof hipayPaypalConfig.createHipayAvailablePaymentProducts ===
                'function' &&
                typeof hipayConfig !== 'undefined' &&
                hipayConfig.getApiUsernameTokenJs &&
                hipayConfig.getApiPasswordTokenJs &&
                typeof hipayConfig.getEnv !== 'undefined'
            ) {
                var config = hipayPaypalConfig.createHipayAvailablePaymentProducts(
                    hipayConfig.getApiUsernameTokenJs,
                    hipayConfig.getApiPasswordTokenJs,
                    hipayConfig.getEnv === 'stage'
                )
                if (typeof config?.getAvailablePaymentProducts === 'function') {
                    config
                        .getAvailablePaymentProducts('paypal', '7', '4', 'true')
                        .then(function (result) {
                            toggleFields(
                                result?.length > 0 &&
                                result[0].options.payer_id.length > 0 &&
                                result[0].options.provider_architecture_version === 'v1'
                            )
                        })
                        .catch(function () {
                            toggleFields(false)
                        })
                }
            } else {
                toggleFields(false)
            }
        }
        checkPayPalSelected()
    }

    var debouncedHandler = function () {
        clearTimeout(debounceTimer)
        debounceTimer = setTimeout(handleHiPayPayPalSection, 250)
    }

    debouncedHandler()

    new MutationObserver(debouncedHandler).observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class']
    })

    document.addEventListener('click', function (event) {
        if (event?.target.id === 'payment_us_hipay_paypalapi-head') {
            setTimeout(debouncedHandler, 0)
        }
        if (event?.target.id === 'payment_us_hipay_hosted_paypal-head') {
            setTimeout(debouncedHandler, 0)
        }
    })

    // Add event listener for select field changes
    var select = document.getElementById('payment_us_hipay_hosted_payment_products')
    if (select) {
        select.addEventListener('change', checkPayPalSelected)
    }

    // Initial check on page load
    checkPayPalSelected()
})