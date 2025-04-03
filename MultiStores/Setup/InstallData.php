<?php
/**
 * @category HiPay
 * @package HiPay_MultiStores
 * @copyright Copyright (c) HiPay
 * @license https://www.hipay.com
 */

namespace HiPay\MultiStores\Setup;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class InstallData
 *
 * Setup class for creating secondary website, store group and store view during module installation
 *
 * @package HiPay\MultiStores\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $configResource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var AreaList
     */
    protected $_areaList;

    /**
     * Secondary website code constant
     */
    const SECONDARY_WEBSITE_CODE = 'website2';

    /**
     * Secondary store code constant
     */
    const SECONDARY_STORE_CODE = 'secondary_store';

    /**
     * InstallData constructor.
     *
     * @param WebsiteFactory $websiteFactory
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $configResource
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductFactory $productFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param State $state
     * @param AreaList $areaList
     */
    public function __construct(
        WebsiteFactory $websiteFactory,
        StoreFactory $storeFactory,
        GroupFactory $groupFactory,
        StoreManagerInterface $storeManager,
        Config $configResource,
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        State $state,
        AreaList $areaList
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->storeManager = $storeManager;
        $this->configResource = $configResource;
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_state = $state;
        $this->_areaList = $areaList;
    }

    /**
     * Install method to create secondary website, store group and store view
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->_state->setAreaCode(Area::AREA_ADMINHTML);
        $areaModel = $this->_areaList->getArea($this->_state->getAreaCode());
        // Enable store codes in URLs globally
        $this->configResource->saveConfig(
            'web/url/use_store',
            1,
            'default',
            0
        );

        // Create or load secondary website
        try {
            $website = $this->storeManager->getWebsite(self::SECONDARY_WEBSITE_CODE);
        } catch (\Exception $e) {
            $website = $this->websiteFactory->create();
            $website->setCode(self::SECONDARY_WEBSITE_CODE)
                ->setName('Secondary Website')
                ->save();
        }

        $websiteId = $website->getId();

        // Create store group if not exists
        $group = $this->groupFactory->create();
        $existingGroup = $group->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->getFirstItem();

        if (!$existingGroup->getId()) {
            // Create store first (required for default_store_id)
            $store = $this->storeFactory->create();
            $store->setCode(self::SECONDARY_STORE_CODE)
                ->setWebsiteId($websiteId)
                ->setName('Secondary Store View')
                ->setIsActive(1)
                ->save();

            // Create store group
            $group->setWebsiteId($websiteId)
                ->setName('Secondary Store Group')
                ->setCode('secondary_group')
                ->setRootCategoryId(2) // Verify this exists in your DB
                ->setDefaultStoreId($store->getId())
                ->save();

            // Update relationships
            $website->setDefaultGroupId($group->getId())->save();
            $store->setGroupId($group->getId())->save();
        }

        // Configure base URLs using primary website's URLs as the single source of truth
        $this->configureWebsiteUrls();

        // Ensure proper cookie settings
        $this->configResource->saveConfig(
            'web/cookie/cookie_path',
            '/',
            'websites',
            $websiteId
        );

        $this->configResource->saveConfig(
            'web/cookie/cookie_domain',
            '',
            'websites',
            $websiteId
        );
        $this->assignProductsToWebsite($websiteId);

        $setup->endSetup();
    }

    /**
     * Configure website URLs based on primary website configuration
     *
     * @return void
     */
    private function configureWebsiteUrls()
    {
        // Get current base URLs from primary website configuration
        $baseUrl = $this->scopeConfig->getValue('web/unsecure/base_url');
        $secureBaseUrl = $this->scopeConfig->getValue('web/secure/base_url');

        // Ensure URLs end with a slash
        $baseUrl = rtrim($baseUrl, '/') . '/';
        $secureBaseUrl = rtrim($secureBaseUrl, '/') . '/';

        // Set the same URLs for both websites
        $this->configResource->saveConfig(
            'web/unsecure/base_url',
            $baseUrl,
            'default',
            0
        );

        $this->configResource->saveConfig(
            'web/secure/base_url',
            $secureBaseUrl,
            'default',
            0
        );

        // Secondary website uses the same base URLs
        $this->configResource->saveConfig(
            'web/unsecure/base_url',
            $baseUrl,
            'websites',
            $this->storeManager->getWebsite(self::SECONDARY_WEBSITE_CODE)->getId()
        );

        $this->configResource->saveConfig(
            'web/secure/base_url',
            $secureBaseUrl,
            'websites',
            $this->storeManager->getWebsite(self::SECONDARY_WEBSITE_CODE)->getId()
        );

        $this->configResource->saveConfig(
            'web/unsecure/base_link_url',
            $baseUrl,
            'websites',
            $this->storeManager->getWebsite(self::SECONDARY_WEBSITE_CODE)->getId()
        );
    }

    /**
     * Assign all existing products to the secondary website
     *
     * @param int $websiteId
     * @return void
     */
    private function assignProductsToWebsite($websiteId)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');

        foreach ($productCollection as $product) {
            $websiteIds = $product->getWebsiteIds();
            if (!in_array($websiteId, $websiteIds)) {
                $websiteIds[] = $websiteId;
                $product->setWebsiteIds($websiteIds);
                $product->save();
            }
        }
    }
}