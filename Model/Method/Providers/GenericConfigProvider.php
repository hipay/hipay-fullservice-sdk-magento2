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
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Model\CcConfig;
use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * Class Generic config provider
 * Can be used by all payment method
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
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
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * GenericConfigProvider constructor.
     *
     * @param CcConfig                                                             $ccConfig
     * @param \HiPay\FullserviceMagento\Helper\Data                                $hipayHelper
     * @param \Magento\Customer\Model\Session                                      $customerSession
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory
     * @param Context                                                              $context
     * @param array                                                                $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \Magento\Customer\Model\Session $customerSession,
        \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \Psr\Log\LoggerInterface $logger,
        ResolverInterface $resolver,
        \HiPay\FullserviceMagento\Model\Config $hipayConfig,
        array $methodCodes = []
    ) {
        $this->methods = $methodCodes;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->resolver = $resolver;
        $this->checkoutSession = $context->getCheckoutSession();
        $this->_collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;

        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();
        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);
        $this->_hipayConfig->setMethodCode("");
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
                            'placeOrderStatusUrl' => [
                                $methodCode => $this->urlBuilder->getUrl(
                                    'hipay/payment/placeOrderStatus',
                                    ['_secure' => true]
                                )
                            ],
                            'afterPlaceOrderUrl' => [
                                $methodCode => $this->urlBuilder->getUrl(
                                    'hipay/payment/afterPlaceOrder',
                                    ['_secure' => true]
                                )
                            ],
                            'isIframeMode' => [$methodCode => $this->isIframeMode($methodCode)],
                            'useOneclick' => [$methodCode => $this->useOneclick($methodCode)],
                            'maxSavedCard' => [$methodCode => $this->getMaxSavedCardCount($methodCode)],
                            'displayCardOwner' => [$methodCode => $this->displayCardOwner($methodCode)],
                            'iFrameWidth' => [$methodCode => $this->getIframeProp($methodCode, 'width')],
                            'iFrameHeight' => [$methodCode => $this->getIframeProp($methodCode, 'height')],
                            'iFrameStyle' => [$methodCode => $this->getIframeProp($methodCode, 'style')],
                            'iFrameWrapperStyle' => [$methodCode => $this->getIframeProp($methodCode, 'wrapper_style')],
                            'locale' => [$methodCode => strtolower($this->resolver->getLocale())]
                        ]
                    ]
                ]);
            }
        }
        /** @var $card \HiPay\FullserviceMagento\Model\Card */
        $cards = [];
        foreach ($this->getCustomerCards() as $card) {
            $cards[] = [
                'token' => $card->getCcToken(),
                'brand' => $card->getCcType(),
                'card_holder' => $card->getCcOwner(),
                'card_expiry_month' => $card->getCcExpMonth(),
                'card_expiry_year' => $card->getCcExpYear(),
                'pan' => str_replace('*', 'x', $card->getCcNumberEnc())
            ];
        }

        $config = array_merge_recursive($config, [
            'payment' => [
                'hiPayFullservice' => [
                    'customerCards' => $cards,
                    'selectedCard' => count($cards) ? current($cards)['token'] : null,
                    'defaultEci' => ECI::SECURE_ECOMMERCE,
                    'recurringEci' => ECI::RECURRING_ECOMMERCE,
                    'useOrderCurrency' => (bool)$this->_hipayConfig->useOrderCurrency()
                ]
            ]
        ]);

        return $config;
    }

    protected function displayCardOwner()
    {
        return $this->_hipayConfig->getValue('display_card_owner');
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
        $allowUseOneclick = $this->_hipayConfig->getValue('allow_use_oneclick');

        return (bool)$this->hipayHelper->useOneclick($allowUseOneclick);
    }

    protected function getMaxSavedCardCount($methodCode)
    {
        $maxSavedCards = $this->_hipayConfig->getValue('one_click/max_saved_cards');
        return $maxSavedCards >= 1 ? $maxSavedCards : time();
    }

    protected function isIframeMode($methodCode)
    {
        return (bool)$this->_hipayConfig->getValue('iframe_mode');
    }

    protected function getIframeProp($methodCode, $prop)
    {
        return $this->_hipayConfig->getValue('iframe_' . $prop);
    }
}
