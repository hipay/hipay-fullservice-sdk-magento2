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

namespace HiPay\FullserviceMagento\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Remove obsolete shipping mapping index
 */
class RemoveObsoleteShippingIndex implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Apply patch
     *
     * @return RemoveObsoleteShippingIndex
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $connection = $this->schemaSetup->getConnection();
        $tableName = $this->schemaSetup->getTable('hipay_cart_mapping_shipping');

        // Remove obsolete index if it exists
        if ($connection->isTableExists($tableName)) {
            $indexName = 'MAGE_MAGENTO_SHIPPING_CODE_MAGENTO_SHIPPING_CODE';

            // Check if index exists before dropping
            $indexes = $connection->getIndexList($tableName);
            if (isset($indexes[$indexName])) {
                $connection->dropIndex($tableName, $indexName);
            }
        }

        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
