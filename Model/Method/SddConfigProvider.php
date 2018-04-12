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
namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;

/**
 * SDD config provider
 *
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SddConfigProvider implements ConfigProviderInterface
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
    protected $_collectionFactory;

    /**
     * Cards collection
     *
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection
     */
    protected $_collection;

    /**
     * SddConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param \Magento\Framework\Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Url $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory,
        array $methodCodes = []
    ) {
        $this->ccConfig = $ccConfig;
        foreach ($methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->urlBuilder = $urlBuilder;
        $this->hipayHelper = $hipayHelper;
        $this->checkoutSession = $checkoutSession;
        $this->_collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Get flag electronic_signature in config
     *
     * @param $methodCode
     * @return bool
     */
    protected function useElectronicSignature($methodCode)
    {
        return (bool)$this->methods[$methodCode]->getConfigData('electronic_signature');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode => $method) {
            if ($method->isAvailable()) {
                $config = array_merge_recursive($config, [
                    'payment' => [
                        'hiPayFullservice' => [
                            'useElectronicSignature' => [$methodCode => $this->useElectronicSignature($methodCode)],
                        ]
                    ]
                ]);
            }
        }
        return $config;
    }
}
