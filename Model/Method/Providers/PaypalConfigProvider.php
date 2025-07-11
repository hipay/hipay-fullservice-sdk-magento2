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

use HiPay\FullserviceMagento\Helper\Data;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Method\Context;
use HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Model\CcConfig;
use Psr\Log\LoggerInterface;

/**
 * Paypal config provider
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaypalConfigProvider extends AbstractConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     *
     * @var Data
     */
    protected $hipayHelper;

    /**
     *
     * @var Session
     */
    protected $customerSession;

    /**
     * Card resource model
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     *
     * @var Config
     */
    protected $hipayConfig;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * PaypalConfigProvider constructor.
     *
     * @param CcConfig          $ccConfig
     * @param Data              $hipayHelper
     * @param Session           $customerSession
     * @param CollectionFactory $collectionFactory
     * @param Context           $context
     * @param LoggerInterface   $logger
     * @param ResolverInterface $resolver
     * @param Config            $hipayConfig
     * @param array             $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        Data $hipayHelper,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        Context $context,
        LoggerInterface $logger,
        ResolverInterface $resolver,
        Config $hipayConfig,
        array $methodCodes = []
    ) {
        parent::__construct(
            $context,
            $logger
        );

        $this->methods = $methodCodes;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->resolver = $resolver;
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;

        $storeId = $this->resolveValidStoreId();

        $this->hipayConfig = $hipayConfig;
        $this->hipayConfig->setStoreId($storeId);
        $this->hipayConfig->setMethodCode('');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
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
                                'button_label' => $this->hipayConfig->getValue('button_label'),
                                'button_layout' => $this->hipayConfig->getValue('button_layout'),
                                'button_color' => $this->hipayConfig->getValue('button_color'),
                                'button_height' => $this->hipayConfig->getValue('button_height'),
                                'button_shape' => $this->hipayConfig->getValue('button_shape'),
                                'bnpl' => (bool) $this->hipayConfig->getValue('bnpl')
                            ],
                        ],
                    ]
                );
            }
        }

        return $config;
    }
}
