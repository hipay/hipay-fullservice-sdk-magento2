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
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Config
     */
    protected $_hipayConfig;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var Json
     */
    protected $_json;

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
     * HipayAvailablePaymentProducts constructor.
     *
     * @param LoggerInterface $logger
     * @param Config $hipayConfig
     * @param ResponseInterface $response
     * @param Json $json
     */
    public function __construct(
        LoggerInterface $logger,
        Config $hipayConfig,
        ResponseInterface $response,
        Json $json
    ) {
        $this->_logger = $logger;
        $this->_hipayConfig = $hipayConfig;
        $this->_response = $response;
        $this->_json = $json;
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
     * Get available payment products.
     *
     * @param string $paymentProduct
     * @param string $eci
     * @param string $operation
     * @param string $withOptions
     * @return array|bool
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
            $errorCode = curl_errno($ch);
            $errorMessage = 'Curl error: ' . curl_error($ch);
            $this->_logger->critical($errorMessage, ['error_code' => $errorCode]);

            $errorResponse = [
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ];

            $this->_response->setHeader('Content-Type', 'application/json');
            $this->_response->setBody($this->_json->serialize($errorResponse));
            $this->_response->sendResponse();

            return false;
        }

        curl_close($ch);

        return $this->_json->unserialize($response);
    }
}
