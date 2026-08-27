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

namespace HiPay\FullserviceMagento\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Enable multi payment products for Apple Pay and migrate the legacy "cb"
 * value to the supported products list.
 *
 * Replaces the 1.33.0 block of Setup/UpgradeData.php. Brand new installs rely
 * on the defaults declared in etc/config.xml; this patch only migrates existing
 * merchant configuration.
 *
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class UpdateApplePayPaymentProducts implements DataPatchInterface
{
    /**
     * @var string
     */
    private const PATH_IS_MULTI = 'payment/hipay_applepay/is_multi_payment_products';

    /**
     * @var string
     */
    private const PATH_PAYMENT_PRODUCTS = 'payment/hipay_applepay/payment_products';

    /**
     * Legacy single value to migrate from.
     *
     * @var string
     */
    private const LEGACY_PAYMENT_PRODUCTS = 'cb';

    /**
     * Supported Apple Pay payment products.
     *
     * @var string
     */
    private const SUPPORTED_PAYMENT_PRODUCTS = 'visa,mastercard,cb,maestro';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface          $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->enableMultiPaymentProducts();
        $this->migrateLegacyPaymentProducts();

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * Enable multi payment products on every configured scope, or seed the
     * default scope when no configuration row exists yet.
     *
     * @return void
     */
    private function enableMultiPaymentProducts()
    {
        $scopes = $this->fetchScopes(self::PATH_IS_MULTI);

        if (!$scopes) {
            $this->configWriter->save(self::PATH_IS_MULTI, '1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            return;
        }

        foreach ($scopes as $scope) {
            $this->configWriter->save(self::PATH_IS_MULTI, '1', $scope['scope'], (int) $scope['scope_id']);
        }
    }

    /**
     * Migrate the legacy "cb" value to the supported products list on every
     * configured scope, or seed the default scope when no row exists yet.
     *
     * @return void
     */
    private function migrateLegacyPaymentProducts()
    {
        foreach ($this->fetchScopes(self::PATH_PAYMENT_PRODUCTS, self::LEGACY_PAYMENT_PRODUCTS) as $scope) {
            $this->configWriter->save(
                self::PATH_PAYMENT_PRODUCTS,
                self::SUPPORTED_PAYMENT_PRODUCTS,
                $scope['scope'],
                (int) $scope['scope_id']
            );
        }

        if (!$this->pathExists(self::PATH_PAYMENT_PRODUCTS)) {
            $this->configWriter->save(
                self::PATH_PAYMENT_PRODUCTS,
                self::SUPPORTED_PAYMENT_PRODUCTS,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
    }

    /**
     * Fetch the scopes holding a configuration row for the given path
     * (optionally filtered by an exact current value).
     *
     * @param  string      $path
     * @param  string|null $value
     * @return array
     */
    private function fetchScopes($path, $value = null)
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['scope', 'scope_id'])
            ->where('path = ?', $path);

        if ($value !== null) {
            $select->where('value = ?', $value);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Whether at least one configuration row exists for the given path.
     *
     * @param  string $path
     * @return bool
     */
    private function pathExists($path)
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['config_id'])
            ->where('path = ?', $path)
            ->limit(1);

        return (bool) $connection->fetchOne($select);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
