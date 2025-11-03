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

namespace HiPay\FullserviceMagento\Helper;

use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Gateway\Manager;
use HiPay\FullserviceMagento\Model\RuleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Main Helper class
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Data extends AbstractHelper
{
    protected const MODULE_NAME = 'HiPay_FullserviceMagento';

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\RuleFactory $ruleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param ResourceInterface $moduleResource
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface $moduleList
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        \HiPay\FullserviceMagento\Model\RuleFactory $ruleFactory,
        ResourceInterface $moduleResource,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
        $this->moduleResource = $moduleResource;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->customerSession = $customerSession;
    }

    /**
     * Determine the 3D Secure mode based on configuration and rule validation.
     *
     * @param bool $use3dSecure
     * @param int $config3dsRules
     * @param \Magento\Quote\Model\Quote $quote
     * @return int
     */
    public function is3dSecure($use3dSecure, $config3dsRules, $quote = null)
    {
        $params = 0;
        if ($use3dSecure > 0 && $quote === null) {
            $params = 1;
        } else {
            switch ((int)$use3dSecure) {
                case 1:
                    $params = 1;
                    break;
                case 2:
                case 3:
                    /**
                     * @var $rule Allopass_Hipay_Model_Rule *
                     */
                    $rule = $this->ruleFactory->create();
                    $rule->load($config3dsRules);
                    if ($rule->getId() && $rule->validate($quote)) {
                        $params = 1;
                        //case for force 3ds if rules are validated
                        if ((int)$use3dSecure == 3) {
                            $params = 2;
                        }
                    }
                    break;
                case 4:
                    $params = 2;
                    break;
            }
        }
        return $params;
    }

    /**
     * Check if one-click payment is allowed for the current customer session.
     *
     * @param  bool $allowUseOneclick Method config Data
     * @return boolean
     */
    public function useOneclick($allowUseOneclick)
    {
        return $this->customerSession->isLoggedIn() && (bool)$allowUseOneclick;
    }

    /**
     *  Generate JSON Structure for request source field (Technical Field)
     *
     * @return string
     */
    public function getRequestSource()
    {
        $version = '';
        if ($this->moduleResource) {
            $version = $this->moduleResource->getDbVersion('HiPay_FullserviceMagento');
        }

        $request = [
            'source' => 'CMS',
            'brand' => 'magento',
            'brand_version' => $this->productMetadata->getVersion(),
            'integration_version' => $version
        ];

        return json_encode($request);
    }

    /**
     * Check if the transaction should use the order currency based on store configuration.
     *
     * @return bool
     */
    public function useOrderCurrency()
    {

        return (bool)$this->scopeConfig->getValue(
            'hipay/configurations/currency_transaction',
            ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Update the hash algorithm in configuration using gateway security settings
     *
     * @param Config $config
     * @param Manager $gatewayClient
     * @param StoreInterface $store
     * @param string $scope
     * @return mixed
     */
    public function updateHashAlgorithm(
        Config $config,
        Manager $gatewayClient,
        StoreInterface $store,
        $scope = ScopeInterface::SCOPE_STORES
    ) {
        $hash = $gatewayClient->requestSecuritySettings();
        $config->setHashingAlgorithm($hash, $scope);
        $store->resetConfig();
        return $hash;
    }

    /**
     * Load and enrich version metadata from configuration.
     *
     * @param Config $config
     * @return mixed|\stdClass|string
     */
    public function readVersionDataFromConf(
        Config $config
    ) {
        $info = $config->getVersionInfo();

        if (!$info || !is_string($info)) {
            $info = new \stdClass();
        } elseif (is_string($info)) {
            $info = json_decode($info ?: '');
        }

        $info->version = $this->getExtensionVersion();

        return $info;
    }

    /**
     * Retrieve the current setup version of the HiPay extension.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}
