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
use HiPay\FullserviceMagento\Model\Method\CcSplitMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Generic config provider
 * Can bu used by all SPLIT payment method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SplitConfigProvider implements ConfigProviderInterface
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
     *
     * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
     */
    protected $_hipayConfig;

    /**
     * SplitConfigProvider constructor.
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     * @param \HiPay\FullserviceMagento\Model\Method\Context $context
     * @param array $methodCodes
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $ppCollectionFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        array $methodCodes = []
    ) {

        foreach ($methodCodes as $code) {
            $this->methods[$code] = $context->getPaymentData()->getMethodInstance($code);
        }
        $this->ppCollectionFactory = $ppCollectionFactory;
        $this->checkoutSession = $context->getCheckoutSession();
        $this->checkoutHelper = $checkoutHelper;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->hipayHelper = $hipayHelper;
        $this->priceCurrency = $context->getPriceCurrency();
        $this->_hipayConfig = $context->getConfigFactory()->create();

    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {

        $config = [];
        foreach ($this->methods as $methodCode => $method) {
            if ($method->isAvailable()) {
                $config = array_merge_recursive(
                    $config,
                    [
                        'payment' => [
                            'hipaySplit' => [
                                'paymentProfiles' => [$methodCode => $this->getPaymentProfilesAsArray($methodCode)],
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
     * @param string $methodCode
     * @return \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\Collection
     */
    protected function getPaymentProfiles($methodCode)
    {

        if (!isset($this->paymentProfiles[$methodCode])) {

            $ppIds = $this->methods[$methodCode]->getConfigData('split_payments');
            if (!is_array($ppIds)) {
                $ppIds = explode(',', $ppIds);
            }
            $this->paymentProfiles[$methodCode] = $this->ppCollectionFactory->create();
            $this->paymentProfiles[$methodCode]->addFieldToFilter('profile_id', array('IN' => $ppIds));
        }

        return $this->paymentProfiles[$methodCode];
    }

    /**
     *
     * @param string $methodCode
     * @return []
     */
    protected function getPaymentProfilesAsArray($methodCode)
    {
        $pProfiles = [];

        /** @var $pp \HiPay\FullserviceMagento\Model\PaymentProfile */
        foreach ($this->getPaymentProfiles($methodCode) as $pp) {

            $amounts = $this->checkoutSession->getQuote()->getBaseGrandTotal();
            $currency = $this->checkoutSession->getQuote()->getStore()->getBaseCurrency();
            if ($this->hipayHelper->useOrderCurrency()) {
                $amounts = $this->checkoutSession->getQuote()->getGrandTotal();
                $currency = null;
            }

            $splitAmounts = $pp->splitAmount($amounts);

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
