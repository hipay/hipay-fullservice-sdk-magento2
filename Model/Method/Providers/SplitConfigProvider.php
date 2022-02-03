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
use HiPay\FullserviceMagento\Model\Method\CcSplitMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use HiPay\FullserviceMagento\Model\Method\Providers\CcConfigProvider;

/**
 * Class Generic config provider
 * Can bu used by all SPLIT payment method
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SplitConfigProvider extends CcConfigProvider
{
    /**
     * @var string $methodCode
     */
    protected $methodCode = CcSplitMethod::HIPAY_METHOD_CODE;

    /**
     * @var MethodInterface[]
     */
    protected $methods = [];

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory
     */
    protected $ppCollectionFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\Collection[] $paymentProfiles
     */
    protected $paymentProfiles = [];

    /**
     *
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @var \Magento\Checkout\Helper\Data $checkoutHelper
     */
    protected $checkoutHelper;

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
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * SplitConfigProvider constructor.
     *
     * @param  \Magento\Payment\Model\CcConfig                                                $ccConfig
     * @param  \Magento\Payment\Helper\Data                                                   $paymentHelper
     * @param  \Magento\Framework\Url                                                         $urlBuilder
     * @param  \HiPay\FullserviceMagento\Model\System\Config\Source\CcType                    $cctypeSource
     * @param  \HiPay\FullserviceMagento\Model\Config\Factory                                 $configFactory
     * @param  \Magento\Framework\View\Asset\Source                                           $assetSource
     * @param  \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory
     * @param  \Magento\Checkout\Helper\Data                                                  $checkoutHelper
     * @param  \HiPay\FullserviceMagento\Helper\Data                                          $hipayHelper
     * @param  Context                                                                        $context
     * @param  array                                                                          $methodCodes
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        \HiPay\FullserviceMagento\Model\System\Config\Source\CcType $cctypeSource,
        \Magento\Framework\View\Asset\Source $assetSource,
        \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Model\Config $hipayConfig,
        array $methodCodes = []
    ) {
        parent::__construct($ccConfig, $cctypeSource, $assetSource, $context, $logger, $hipayConfig);

        $this->methods = $methodCodes;
        $this->ppCollectionFactory = $ppCollectionFactory;
        $this->checkoutSession = $context->getCheckoutSession();
        $this->checkoutHelper = $checkoutHelper;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->priceCurrency = $context->getPriceCurrency();
        $this->checkoutSession = $context->getCheckoutSession();
        $storeId = $this->checkoutSession->getQuote()->getStore()->getStoreId();

        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);
        $this->_hipayConfig->setMethodCode("");
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode) {
            $this->_hipayConfig->setMethodCode($methodCode);
            if ($this->_hipayConfig->isPaymentMethodActive()) {
                $config = array_merge_recursive(
                    $config,
                    [
                        'payment' => [
                            'hipaySplit' => [
                                'paymentProfiles' => [$methodCode => $this->getPaymentProfilesAsArray($methodCode)],
                                'apiUsernameTokenJs' => [$methodCode => $this->_hipayConfig->getApiUsernameTokenJs()],
                                'apiPasswordTokenJs' => [$methodCode => $this->_hipayConfig->getApiPasswordTokenJs()],
                                'availableTypes' => [$methodCode => $this->getCcAvailableTypesOrdered()],
                                'env' => [$methodCode => $this->_hipayConfig->getApiEnv()],
                                'icons' => [$methodCode => $this->getIcons()]
                            ]
                        ]
                    ]
                );
            }
        }

        $config['payment']['hipaySplit']['refreshConfigUrl'] = $this->urlBuilder->getUrl(
            'hipay/payment/refreshCheckoutConfig'
        );

        return $config;
    }

    /**
     *
     * @param  string $methodCode
     * @return \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\Collection
     */
    protected function getPaymentProfiles($methodCode)
    {
        if (!isset($this->paymentProfiles[$methodCode])) {
            $ppIds = $this->_hipayConfig->getValue('split_payments');
            if (!is_array($ppIds)) {
                $ppIds = explode(',', $ppIds);
            }
            $this->paymentProfiles[$methodCode] = $this->ppCollectionFactory->create();
            $this->paymentProfiles[$methodCode]->addFieldToFilter('profile_id', array('IN' => $ppIds));
        }

        return $this->paymentProfiles[$methodCode];
    }

    /**
     * @param  $methodCode
     * @return array
     */
    protected function getPaymentProfilesAsArray($methodCode)
    {
        $pProfiles = [];

        /**
         * @var $pp \HiPay\FullserviceMagento\Model\PaymentProfile
        */
        foreach ($this->getPaymentProfiles($methodCode) as $pp) {
            $amounts = $this->checkoutSession->getQuote()->getBaseGrandTotal();
            $currency = $this->checkoutSession->getQuote()->getStore()->getBaseCurrency();
            if ($this->hipayHelper->useOrderCurrency()) {
                $amounts = $this->checkoutSession->getQuote()->getGrandTotal();
                $currency = null;
            }

            $splitAmounts = $pp->splitAmount($amounts, new \DateTime());

            foreach ($splitAmounts as $index => $split) {
                $date = new \DateTime($split['dateToPay']);
                $splitAmounts[$index]['dateToPayFormatted'] = $date->format('d/m/Y');
                $splitAmounts[$index]['amountToPayFormatted'] = $this->priceCurrency->format(
                    $split['amountToPay'],
                    true,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $this->checkoutSession->getQuote()->getStore(),
                    $currency
                );
            }

            $pProfiles[] = [
                'name' => $pp->getName(),
                'profileId' => $pp->getProfileId(),
                'splitAmounts' => $splitAmounts

            ];
        }

        return $pProfiles;
    }
}
