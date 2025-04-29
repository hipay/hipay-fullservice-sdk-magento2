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
use Psr\Log\LoggerInterface;
use Exception;

/**
 * PaypalConfigProvider class for PayPal payment product
 *
 * @author    HiPay <support.tpp@hipay.com>
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
     * PaypalConfigProvider Construct
     *
     * @param LoggerInterface $logger
     * @param GatewayClient   $gatewayClient
     */
    public function __construct(
        LoggerInterface $logger,
        GatewayClient $gatewayClient
    ) {
        $this->_logger = $logger;
        $this->_gatewayClient = $gatewayClient;
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
     * @return bool
     */
    protected function isPayPalV2(): bool
    {
        try {
            $paymentProduct = $this->_gatewayClient->requestPaymentProduct('paypal', true);

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
