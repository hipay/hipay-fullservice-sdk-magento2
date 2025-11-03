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

namespace HiPay\FullserviceMagento\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use HiPay\FullserviceMagento\Model\Gateway\Manager as GatewayClient;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayManagerFactory;
use HiPay\FullserviceMagento\Model\Config as HipayConfig;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * PaypalConfigProvider class for PayPal payment product
 *
 * @copyright Copyright (c) 2018 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaypalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var LoggerInterface $_logger
     */
    protected $_logger;

    /**
     * @var GatewayClient
     */
    protected $_gatewayClient;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var GatewayManagerFactory
     */
    protected $_gatewayManagerFactory;

    /**
     * @var HipayConfig
     */
    protected $_hipayConfig;

    /**
     * PaypalConfigProvider Construct
     *
     * @param LoggerInterface       $logger
     * @param GatewayClient         $gatewayClient
     * @param StoreManagerInterface $storeManager
     * @param GatewayManagerFactory $gatewayManagerFactory
     * @param HipayConfig           $hipayConfig
     */
    public function __construct(
        LoggerInterface $logger,
        GatewayClient $gatewayClient,
        StoreManagerInterface $storeManager,
        GatewayManagerFactory $gatewayManagerFactory,
        HipayConfig $hipayConfig
    ) {
        $this->_logger = $logger;
        $this->_gatewayClient = $gatewayClient;
        $this->_storeManager = $storeManager;
        $this->_gatewayManagerFactory = $gatewayManagerFactory;
        $this->_hipayConfig = $hipayConfig;
    }

    /**
     * Function getConfig
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'hipay_paypalapi' => [
                    'isPayPalV2' => (int) $this->isPayPalV2()
                ]
            ]
        ];
    }

    /**
     * Check if PayPal V2 is being used.
     *
     * @param int $storeId
     * @return bool
     */
    protected function isPayPalV2($storeId = null): bool
    {
        try {
            $storeId = $storeId ?? $this->_storeManager->getStore()->getId();
            $gatewayClient = $this->_gatewayManagerFactory->create(
                null,
                [
                    'apiEnv' => $this->_hipayConfig->getApiEnv(),
                    'storeId' => $storeId
                ]
            );
            $paymentProduct = $gatewayClient->requestPaymentProduct('paypal', true);

            if (!empty($paymentProduct[0]->getOptions())) {
                $options = $paymentProduct[0]->getOptions();
                return $options['providerArchitectureVersion'] === 'v1' && !empty($options['payerId']);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return false;
    }
}
