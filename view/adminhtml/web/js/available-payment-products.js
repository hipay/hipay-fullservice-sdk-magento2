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
define(['domReady!'], function () {
  'use strict';

  // The module needs to return something directly
  return function availablePaymentProducts() {
    const createConfig = () => ({
      operation: ['4'],
      payment_product: [],
      eci: ['7'],
      with_options: false,
      customer_country: [],
      currency: [],
      payment_product_category: [],
      apiUsername: '',
      apiPassword: '',
      baseUrl: '',
      authorizationHeader: ''
    });

    const generateAuthorizationHeader = (username, password) => {
      const credentials = `${username}:${password}`;
      const encodedCredentials = btoa(credentials);
      return `Basic ${encodedCredentials}`;
    };

    const toQueryString = (config) => {
      const params = {
        operation: config.operation,
        payment_product: config.payment_product,
        eci: config.eci,
        with_options: config.with_options ? 'true' : 'false',
        customer_country: config.customer_country,
        currency: config.currency,
        payment_product_category: config.payment_product_category
      };

      const filteredParams = Object.entries(params).reduce(
        (acc, [key, value]) => {
          if (Array.isArray(value) && value.length > 0) {
            acc[key] = value.join(',');
          } else if (value !== '' && value !== false && value.length !== 0) {
            acc[key] = value;
          }
          return acc;
        },
        {}
      );

      return new URLSearchParams(filteredParams).toString();
    };

    let config = createConfig();

    return {
      setCredentials: function (username, password, isSandbox = false) {
        config.apiUsername = username;
        config.apiPassword = password;
        config.baseUrl = isSandbox
          ? 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/'
          : 'https://secure-gateway.hipay-tpp.com/rest/v2/';
        config.authorizationHeader = generateAuthorizationHeader(
          username,
          password
        );
      },

      updateConfig: function (key, value) {
        config[key] = value;
      },

      getAvailableProducts: async function () {
        if (!config.baseUrl || !config.authorizationHeader) {
          throw new Error(
            'Credentials must be set before calling getAvailableProducts'
          );
        }

        const url = new URL(`${config.baseUrl}available-payment-products.json`);
        url.search = toQueryString(config);

        try {
          const response = await fetch(url, {
            method: 'GET',
            headers: {
              Authorization: config.authorizationHeader,
              Accept: 'application/json'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          return await response.json();
        } catch (error) {
          console.error('There was a problem with the fetch operation:', error);
          throw error;
        }
      }
    };
  };
});
