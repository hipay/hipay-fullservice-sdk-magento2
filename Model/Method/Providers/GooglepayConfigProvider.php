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

namespace HiPay\FullserviceMagento\Model\Method\Providers;

use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Method\Context;
use Magento\Checkout\Model\ConfigProviderInterface;
use Psr\Log\LoggerInterface;

class GooglepayConfigProvider extends AbstractConfigProvider implements ConfigProviderInterface
{
    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var Config
     */
    protected $hipayConfig;

    /**
     * GooglepayConfigProvider constructor.
     *
     * @param Context         $context
     * @param LoggerInterface $logger
     * @param Config          $hipayConfig
     * @param array           $methodCodes
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Config $hipayConfig,
        array $methodCodes = []
    ) {
        parent::__construct($context, $logger);

        $this->hipayConfig = $hipayConfig;
        $this->methods = $methodCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $storeId = $this->resolveValidStoreId();
        $this->hipayConfig->setStoreId($storeId);

        foreach ($this->methods as $methodCode) {
            $this->hipayConfig->setMethodCode($methodCode);
            if ($this->hipayConfig->isPaymentMethodActive()) {
                $config = array_merge_recursive(
                    $config,
                    [
                        'payment' => [
                            $methodCode => [
                                'apiUsernameTokenJs' => $this->hipayConfig->getApiUsernameTokenJs(),
                                'apiPasswordTokenJs' => $this->hipayConfig->getApiPasswordTokenJs(),
                                'env' => $this->hipayConfig->getApiEnv(),
                                'sdkJsUrl' => $this->hipayConfig->getSdkJsUrl(),
                                'display_name' => $this->getDisplayName($storeId),
                            ],
                        ],
                    ]
                );
            }
        }

        return $config;
    }

    /**
     * Display name from config, store name if empty
     *
     * @param  int $storeId
     * @return string
     */
    private function getDisplayName($storeId)
    {
        $displayName = (string) $this->hipayConfig->getValue('display_name');

        return $displayName !== '' ? $displayName : $this->storeManager->getStore($storeId)->getFrontendName();
    }
}
