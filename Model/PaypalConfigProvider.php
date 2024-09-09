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

use Psr\Log\LoggerInterface;
use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use HiPay\FullserviceMagento\Model\Api\HipayAvailablePaymentProducts;
use HiPay\FullserviceMagento\Model\Method\HostedMethod;

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
     *
     * @var HipayAvailablePaymentProducts $_hipayAvailablePaymentProducts
     */
    protected $_hipayAvailablePaymentProducts;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * PaypalConfigProvider Construct
     *
     * @param LoggerInterface               $logger
     * @param ScopeConfigInterface          $scopeConfig
     * @param HipayAvailablePaymentProducts $hipayAvailablePaymentProducts
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        HipayAvailablePaymentProducts $hipayAvailablePaymentProducts
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_hipayAvailablePaymentProducts = $hipayAvailablePaymentProducts;
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
                    'isPayPalV2' => $this->isPayPalV2() ? 1 : 0,
                    'isHostedField' => $this->isHostedPage() ? 0 : 1
                ]
            ]
        ];
    }

    /**
     * Check if PayPal V2 is being used.
     *
     * @return bool
     */
    protected function isPayPalV2()
    {
        try {
            $paymentProducts = $this->_hipayAvailablePaymentProducts->getAvailablePaymentProducts('paypal');

            if (!empty($paymentProducts[0]['options'])) {
                $options = $paymentProducts[0]['options'];
                return $options['provider_architecture_version'] === 'v1' && !empty($options['payer_id']);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return false;
    }

    /**
     * Check if Hosted Page is Enabled
     *
     * @return bool
     */
    protected function isHostedPage()
    {
       return $this->_scopeConfig->getValue(
            'payment/' . HostedMethod::HIPAY_METHOD_CODE . '/active',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
