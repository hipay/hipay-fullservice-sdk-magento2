<?php

/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */

namespace HiPay\FullserviceMagento\Block\Adminhtml;

use HiPay\FullserviceMagento\Model\Config;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * HipayConfig Block
 *
 * This class handles configuration related to HiPay integration in Magento 2 admin panel.
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HipayConfig extends Template
{
    protected const XML_PATH_HIPAY_CREDENTIALS = 'hipay/hipay_credentials_tokenjs/';
    protected const XML_PATH_HIPAY_PAYMENT_PRODUCT_ENV = 'hipay/hipay_api_environment/api_environment';
    protected const ENV_PRODUCTION = 'production';
    protected const ENV_TEST = 'test';
    protected const ENV_STAGE = 'stage';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $hipayConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * HipayConfig constructor.
     *
     * @param Context               $context
     * @param ScopeConfigInterface  $scopeConfig
     * @param Config                $hipayConfig
     * @param StoreManagerInterface $storeManager
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Config $hipayConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->hipayConfig = $hipayConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get API username for TokenJS
     *
     * @return string
     */
    public function getApiUsernameTokenJs()
    {
        $env = $this->getEnv();
        $configPath = self::XML_PATH_HIPAY_CREDENTIALS .
            ($env === self::ENV_PRODUCTION ? 'api_username' : 'api_username_test');
        return (string) $this->scopeConfig->getValue($configPath, 'store', $this->getCurrentStoreId());
    }

    /**
     * Get API password for TokenJS
     *
     * @return string|null
     */
    public function getApiPasswordTokenJs()
    {
        $env = $this->getEnv();
        $configPath = self::XML_PATH_HIPAY_CREDENTIALS .
            ($env === self::ENV_PRODUCTION ? 'api_password' : 'api_password_test');
        return $this->scopeConfig->getValue($configPath, 'store', $this->getCurrentStoreId());
    }

    /**
     * Get current environment
     *
     * @return string
     */
    public function getEnv()
    {
        $env = $this->scopeConfig
            ->getValue(self::XML_PATH_HIPAY_PAYMENT_PRODUCT_ENV, 'store', $this->getCurrentStoreId());
        return $env === self::ENV_STAGE ? self::ENV_STAGE : $env;
    }

    /**
     * Get current store ID
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        try {
            return (int) $this->storeManager->getStore()->getId();
        } catch (\Exception $e) {
            // Log the exception or handle it as appropriate for your application
            return 0;
        }
    }
}
