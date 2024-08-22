<?php

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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Api;

use HiPay\FullserviceMagento\Model\Config;

/**
 * HipayAvailablePaymentProducts class for payment products
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright Copyright (c) 2018 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HipayAvailablePaymentProducts
{
    /**
     * @var Config $_hipayConfig
     */
    protected $_hipayConfig;

    /**
     * @var string
     */
    protected $apiUsername;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * @var string
     */
    protected $authorizationHeader;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * HipayAvailablePaymentProducts Construct
     *
     * @param Config $hipayConfig
     */
    public function __construct(Config $hipayConfig)
    {
        $this->_hipayConfig = $hipayConfig;
    }

    /**
     * Set the API credentials and base URL based on the environment.
     *
     * @return void
     */
    protected function setCredentialsAndUrl(): void
    {
        $this->apiUsername = $this->_hipayConfig->getApiUsernameTokenJs();
        $this->apiPassword = $this->_hipayConfig->getApiPasswordTokenJs();
        $this->baseUrl = $this->_hipayConfig->getApiEnv() === 'stage'
            ? 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/'
            : 'https://secure-gateway.hipay-tpp.com/rest/v2/';
    }

    /**
     * Generate the Authorization header for API requests.
     *
     * @return void
     */
    protected function generateAuthorizationHeader()
    {
        $credentials = $this->apiUsername . ':' . $this->apiPassword;
        $encodedCredentials = base64_encode($credentials);
        $this->authorizationHeader = 'Basic ' . $encodedCredentials;
    }

    /**
     * Build the URL for the API request then Send the HTTP request to the API.
     *
     * @param $paymentProduct
     * @param $eci
     * @param $operation
     * @param $withOptions
     * @return mixed
     */
    public function getAvailablePaymentProducts(
        $paymentProduct = 'paypal',
        $eci = '7',
        $operation = '4',
        $withOptions = 'true'
    ) {
        $this->setCredentialsAndUrl();
        $this->generateAuthorizationHeader();

        $url = $this->baseUrl . 'available-payment-products.json';
        $url .= '?eci=' . urlencode($eci);
        $url .= '&operation=' . urlencode($operation);
        $url .= '&payment_product=' . urlencode($paymentProduct);
        $url .= '&with_options=' . urlencode($withOptions);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->authorizationHeader,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
