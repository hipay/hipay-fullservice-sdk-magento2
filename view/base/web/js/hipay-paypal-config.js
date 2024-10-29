define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return {
        createHipayAvailablePaymentProducts: function(apiUsername, apiPassword, sandbox_mode) {
            var baseUrl;
            var authorizationHeader;

            function setUrl() {
                baseUrl = sandbox_mode
                    ? 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/'
                    : 'https://secure-gateway.hipay-tpp.com/rest/v2/';
            }

            function generateAuthorizationHeader() {
                var credentials = apiUsername + ':' + apiPassword;
                var encodedCredentials = btoa(credentials);
                authorizationHeader = 'Basic ' + encodedCredentials;
            }

            function getAvailablePaymentProducts(
                paymentProduct = 'paypal',
                eci = '7',
                operation = '4',
                withOptions = 'true'
            ) {
                var url = new URL(baseUrl + 'available-payment-products.json');
                url.searchParams.append('eci', eci);
                url.searchParams.append('operation', operation);
                url.searchParams.append('payment_product', paymentProduct);
                url.searchParams.append('with_options', withOptions);

                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': authorizationHeader,
                        'Accept': 'application/json'
                    }
                })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .catch(function(error) {
                        console.error('There was a problem with the fetch operation:', error);
                        throw error;
                    });
            }

            // Initialize
            setUrl();
            generateAuthorizationHeader();

            // Return public methods
            return {
                getAvailablePaymentProducts: getAvailablePaymentProducts
            };
        }
    };
});