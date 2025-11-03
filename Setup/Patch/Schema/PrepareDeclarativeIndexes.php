<?php
/**
 * HiPay Fullservice Magento
 *
 * Universal Schema Patch for Declarative Migration.
 *
 * - Ensures all HiPay indexes are present and correctly named.
 * - Avoids "Duplicate key name" SQL errors when migrating from UpgradeSchema-based installs.
 * - Works across environments (handles hashed index names).
 *
 * @author HiPay
 */

namespace HiPay\FullserviceMagento\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * PrepareDeclarativeIndexes Patch
 *
 * This patch ensures that all HiPay tables have valid indexes and
 * that duplicates are avoided when transitioning to declarative schema.
 */
class PrepareDeclarativeIndexes implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply schema corrections and ensure proper index consistency.
     *
     * @return $this
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $indexes = [
            // hipay_customer_card: unique index
            'hipay_customer_card' => [
                [
                    'columns' => ['customer_id', 'cc_number_enc'],
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                ],
            ],

            // hipay_cart_mapping_categories: unique index
            'hipay_cart_mapping_categories' => [
                [
                    'columns' => ['category_magento_id'],
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                ],
            ],

            // hipay_notification: regular index
            'hipay_notification' => [
                [
                    'columns' => ['state', 'attempts', 'status', 'created_at', 'order_id'],
                    'type' => AdapterInterface::INDEX_TYPE_INDEX,
                ],
            ],

            // hipay_sales_order: regular index
            'hipay_sales_order' => [
                [
                    'columns' => ['order_id'],
                    'type' => AdapterInterface::INDEX_TYPE_INDEX,
                ],
            ],
        ];

        /** --------------------------------------------------------
         * Iterate over each HiPay table and ensure indexes exist
         * -------------------------------------------------------- */
        foreach ($indexes as $table => $indexList) {
            $tableName = $this->moduleDataSetup->getTable($table);
            if (!$connection->isTableExists($tableName)) {
                continue;
            }

            $existingIndexes = $connection->getIndexList($tableName);

            foreach ($indexList as $indexDef) {
                $columns = $indexDef['columns'];
                $type = $indexDef['type'];

                $expectedIndexName = $connection->getIndexName($tableName, $columns, $type);

                // Check if an equivalent index already exists
                $indexExists = false;
                foreach ($existingIndexes as $existingName => $existingData) {
                    $existingCols = $existingData['COLUMNS_LIST'] ?? [];
                    sort($existingCols);
                    $compareCols = $columns;
                    sort($compareCols);
                    if ($existingCols === $compareCols) {
                        $indexExists = true;
                        break;
                    }
                }

                // Create index only if missing
                if (!$indexExists) {
                    $connection->addIndex(
                        $tableName,
                        $expectedIndexName,
                        $columns,
                        $type
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
