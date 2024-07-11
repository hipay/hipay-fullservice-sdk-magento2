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

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Paypal config provider
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaypalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var MethodInterface[]
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
     * @var \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     */
    protected $hipayHelper;

    /**
     *
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * Card resource model
     *
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory
     */
    protected $collectionFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Config $hipayConfig
     */
    protected $hipayConfig;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * PaypalConfigProvider constructor.
     *
     * @param \Magento\Payment\Model\CcConfig                                      $ccConfig
     * @param \HiPay\FullserviceMagento\Helper\Data                                $hipayHelper
     * @param \Magento\Customer\Model\Session                                      $customerSession
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory
     * @param Context                                                              $context
     * @param array                                                                $methodCodes
     */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \Magento\Customer\Model\Session $customerSession,
        \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \HiPay\FullserviceMagento\Model\Config $hipayConfig,
        array $methodCodes = []
    ) {
        $this->methods = $methodCodes;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->resolver = $resolver;
        $this->checkoutSession = $context->getCheckoutSession();
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;

        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();
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
                                'merchant_id' => $this->hipayConfig->getValue('merchant_id'),
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
