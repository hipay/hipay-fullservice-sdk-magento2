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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Model\Method\Providers;

use HiPay\FullserviceMagento\Model\Method\Context;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;

/**
 * Astropay config provider
 *
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AstropayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var string[]
     */
    protected $methods = [];

    /**
     * @var string[]
     */
    protected $methodTypeIdentification = [];

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
    protected $_collectionFactory;

    /**
     * Cards collection
     *
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection
     */
    protected $_collection;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
     */
    protected $_hipayConfig;

    /**
     * AstropayConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param \Magento\Framework\Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory
     * @param array $methodCodes
     * @param array $methodTypeIdentification
     */
    public function __construct(
        CcConfig $ccConfig,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory,
        Context $context,
        \HiPay\FullserviceMagento\Model\Config $hipayConfig,
        array $methodCodes = [],
        array $methodTypeIdentification = []
    ) {
        $this->ccConfig = $ccConfig;
        $this->methods = $methodCodes;
        $this->methodTypeIdentification = $methodTypeIdentification;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->checkoutSession = $checkoutSession;
        $this->_collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;

        $this->checkoutSession = $context->getCheckoutSession();
        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();

        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);
        $this->_hipayConfig->setMethodCode("");
    }

    /**
     * Get Type identification
     *
     * @param $methodCode
     * @return string
     */
    protected function getTypeIdentification($methodCode)
    {
        return (string)$this->methodTypeIdentification[$methodCode];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode) {
            $this->_hipayConfig->setMethodCode($methodCode);
            if ($this->_hipayConfig->isPaymentMethodActive()) {
                $config = array_merge_recursive($config, [
                    'payment' => [
                        'hiPayFullservice' => [
                            'typeIdentification' => [$methodCode => $this->getTypeIdentification($methodCode)],
                        ]
                    ]
                ]);
            }
        }
        return $config;
    }
}
