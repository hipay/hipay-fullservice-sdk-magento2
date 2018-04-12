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
use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * Class Generic config provider
 * Can be used by all payment method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class GenericConfigProvider implements ConfigProviderInterface
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
     *
     * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
     */
    protected $_hipayConfig;

    /**
     * GenericConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param \Magento\Framework\Url $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory
     * @param Context $context
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
        \HiPay\FullserviceMagento\Model\Method\Context $context,
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

        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();

        $this->_hipayConfig = $context->getConfigFactory()->create(['params' => ['storeId' => $storeId]]);
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
                            'afterPlaceOrderUrl' => [
                                $methodCode => $this->urlBuilder->getUrl(
                                    'hipay/payment/afterPlaceOrder',
                                    ['_secure' => true]
                                )
                            ],
                            'isIframeMode' => [$methodCode => $this->isIframeMode($methodCode)],
                            'useOneclick' => [$methodCode => $this->useOneclick($methodCode)],
                            'displayCardOwner' => [$methodCode => $this->displayCardOwner($methodCode)],
                            'iFrameWidth' => [$methodCode => $this->getIframeProp($methodCode, 'width')],
                            'iFrameHeight' => [$methodCode => $this->getIframeProp($methodCode, 'height')],
                            'iFrameStyle' => [$methodCode => $this->getIframeProp($methodCode, 'style')],
                            'iFrameWrapperStyle' => [$methodCode => $this->getIframeProp($methodCode, 'wrapper_style')],
                        ]
                    ]
                ]);
            }
        }
        /** @var $card \HiPay\FullserviceMagento\Model\Card */
        $cards = [];
        foreach ($this->getCustomerCards() as $card) {
            $cards[] = [
                'name' => $card->getName(),
                'ccToken' => $card->getCcToken(),
                'ccType' => $card->getCcType()
            ];
        }

        $config = array_merge_recursive($config, [
            'payment' => [
                'hiPayFullservice' => [
                    'customerCards' => $cards,
                    'selectedCard' => count($cards) ? current($cards)['ccToken'] : null,
                    'defaultEci' => ECI::SECURE_ECOMMERCE,
                    'recurringEci' => ECI::RECURRING_ECOMMERCE,
                    'useOrderCurrency' => (bool)$this->_hipayConfig->useOrderCurrency()
                ]
            ]
        ]);

        return $config;
    }

    protected function displayCardOwner($methodCode)
    {
        return $this->methods[$methodCode]->getConfigData('display_card_owner');
    }

    /**
     * Get cards
     *
     * @return bool|\HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection
     */
    protected function getCustomerCards()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return [];
        }
        if (!$this->_collection) {
            $this->_collection = $this->_collectionFactory->create();
            $this->_collection
                ->filterByCustomerId($customerId)
                ->addOrder('card_id', 'desc')
                ->onlyValid();
        }
        return $this->_collection;
    }

    protected function useOneclick($methodCode)
    {
        $allowUseOneclick = $this->methods[$methodCode]->getConfigData('allow_use_oneclick');
        $filterOneclick = $this->methods[$methodCode]->getConfigData('filter_oneclick');
        $quote = $this->checkoutSession->getQuote();

        return (bool)$this->hipayHelper->useOneclick($allowUseOneclick, $filterOneclick, $quote);
    }

    protected function isIframeMode($methodCode)
    {
        return (bool)$this->methods[$methodCode]->getConfigData('iframe_mode');
    }

    protected function getIframeProp($methodCode, $prop)
    {
        return $this->methods[$methodCode]->getConfigData('iframe_' . $prop);
    }
}
