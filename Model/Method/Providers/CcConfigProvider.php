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
use HiPay\FullserviceMagento\Model\Method\CcMethod;
use HiPay\FullserviceMagento\Model\Method\HostedFieldsMethod;
use HiPay\FullserviceMagento\Model\Method\Context;
use HiPay\FullserviceMagento\Model\System\Config\Source\CcType;
use Magento\Framework\Url;
use Magento\Payment\Model\CcConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Asset\Source;
use Psr\Log\LoggerInterface;

/**
 * Class CC config provider
 * Can bu used by all Cc API payment method
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CcConfigProvider extends AbstractConfigProvider implements ConfigProviderInterface
{
    /**
     * @var array
     */
    protected $methods =  [
        CcMethod::HIPAY_METHOD_CODE,
        HostedFieldsMethod::HIPAY_METHOD_CODE
    ];

    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     *
     * @var CcType
     */
    protected $_cctypeSource;

    /**
     *
     * @var Config
     */
    protected $_hipayConfig;

    /**
     * @var Source
     */
    protected $assetSource;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * CcConfigProvider constructor.
     *
     * @param CcConfig        $ccConfig
     * @param CcType          $cctypeSource
     * @param Source          $assetSource
     * @param Context         $context
     * @param LoggerInterface $logger
     * @param Config          $hipayConfig
     * @param array           $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        CcType $cctypeSource,
        Source $assetSource,
        Context $context,
        LoggerInterface $logger,
        Config $hipayConfig,
        array $methodCodes = []
    ) {
        parent::__construct(
            $context,
            $logger
        );

        $this->urlBuilder = $context->urlBuilder;
        $this->_cctypeSource = $cctypeSource;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->context = $context;

        $storeId = $this->resolveValidStoreId();

        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);
        $this->_hipayConfig->setMethodCode("");
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode) {
            $this->_hipayConfig->setMethodCode($methodCode);
            if ($this->_hipayConfig->isPaymentMethodActive()) {
                $conf = [
                    $methodCode => [
                        'availableTypes' => $this->getCcAvailableTypesOrdered(),
                        'env' => $this->_hipayConfig->getApiEnv(),
                        'apiUsernameTokenJs' => $this->_hipayConfig->getApiUsernameTokenJs(),
                        'apiPasswordTokenJs' => $this->_hipayConfig->getApiPasswordTokenJs(),
                        'icons' => $this->getIcons($methodCode),
                        'sdkJsUrl' => $this->_hipayConfig->getSdkJsUrl()
                    ]
                ];

                if ($methodCode == HostedFieldsMethod::HIPAY_METHOD_CODE) {
                    $oneClickToggleColor = $this->_hipayConfig->getValue('one_click/save_card_toggle_color');
                    $oneClickHighlightColor = $this->_hipayConfig->getValue('one_click/saved_card_highlight_color');
                    $conf = array_merge_recursive(
                        $conf,
                        [
                            $methodCode => [
                                'color' => $this->_hipayConfig->getValue('color'),
                                'fontFamily' => $this->_hipayConfig->getValue('font_family'),
                                'fontSize' => $this->_hipayConfig->getValue('font_size'),
                                'fontWeight' => $this->_hipayConfig->getValue('font_weight'),
                                'placeholderColor' => $this->_hipayConfig->getValue('placeholder_color'),
                                'caretColor' => $this->_hipayConfig->getValue('caret_color'),
                                'iconColor' => $this->_hipayConfig->getValue('icon_color'),
                                'oneClickToggleColor' => $oneClickToggleColor,
                                'oneClickHighlightColor' => $oneClickHighlightColor
                            ]
                        ]
                    );
                } else {
                    $config = array_merge_recursive(
                        $config,
                        [
                            'payment' => [
                                'ccform' => [
                                    'months' => [$methodCode => $this->getCcMonths()],
                                    'years' => [$methodCode => $this->getCcYears()],
                                    'hasVerification' => [$methodCode => $this->hasVerification()],
                                    'cvvImageUrl' => [$methodCode => $this->getCvvImageUrl()]
                                ]
                            ]
                        ]
                    );
                }

                $config = array_merge_recursive(
                    $config,
                    [
                        'payment' => $conf
                    ]
                );
            }
        }

        return $config;
    }

    /**
     * Retrieve availables credit card types and preserve saved order
     *
     * @return array
     */
    protected function getCcAvailableTypesOrdered()
    {
        $types = $this->_cctypeSource->toKeyValue();
        $availableTypes = $this->_hipayConfig->getValue('cctypes');
        if (!is_array($availableTypes)) {
            $availableTypes = explode(",", $availableTypes ?: '');
        }
        $ordered = [];
        foreach ($availableTypes as $key) {
            if (array_key_exists($key, $types)) {
                $ordered[$key] = $types[$key]['label'];
            }
        }

        return $ordered;
    }

    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    protected function getIcons()
    {
        $icons = [];
        $types = $this->getCcAvailableTypesOrdered();
        foreach (array_keys($types) as $code) {
            if (!array_key_exists($code, $icons)) {
                $asset = $this->ccConfig->createAsset(
                    'HiPay_FullserviceMagento::images/cc/' . strtolower($code) . '.png'
                );
                $placeholder = $this->assetSource->findRelativeSourceFilePath($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesizefromstring($asset->getSourceFile());
                    $icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height
                    ];
                }
            }
        }
        return $icons;
    }

    /**
     * Image url
     *
     * @return string
     */
    protected function getCvvImageUrl()
    {
        return $this->ccConfig->getCvvImageUrl();
    }

    /**
     * Expire months
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    /**
     * Expire years
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->ccConfig->getCcYears();
    }

    /**
     * Return true if CVV verification is enabled via the 'useccv' configuration value
     *
     * @return bool
     * @api
     */
    public function hasVerification()
    {
        $configData = $this->_hipayConfig->getValue('useccv');
        if ($configData === null) {
            return true;
        }
        return (bool)$configData;
    }
}
