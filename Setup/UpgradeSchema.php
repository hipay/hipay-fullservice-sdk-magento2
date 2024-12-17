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

namespace HiPay\FullserviceMagento\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade Schema class
 *
 * @codeCoverageIgnore
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            /**
             * Create table 'hipay_rule'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_rule'))
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Rule Id'
                )
                ->addColumn(
                    'method_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    60,
                    ['nullable' => false],
                    'Method Code'
                )
                ->addColumn(
                    'config_path',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    60,
                    ['nullable' => false],
                    'Config path'
                )
                ->addColumn(
                    'conditions_serialized',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Conditions Serialized'
                )
                ->addColumn(
                    'actions_serialized',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Actions Serialized'
                )
                ->addColumn(
                    'product_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Product Ids'
                )
                ->addColumn(
                    'sort_order',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Sort Order'
                )
                ->addColumn(
                    'simple_action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Simple Action'
                );

            $setup->getConnection()->createTable($table);
        }

        $this->installTokenTable($setup, $context);

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $paymentProfileTable = $setup->getTable('hipay_payment_profile');
            /**
             * Create table 'hipay_payment_profile'
             */
            $table = $setup->getConnection()
                ->newTable($paymentProfileTable)
                ->addColumn(
                    'profile_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Profile Id'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false],
                    'Name of Profile'
                )
                ->addColumn(
                    'period_unit',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => false],
                    'Unit of period'
                )
                ->addColumn(
                    'period_frequency',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'unsigned' => true],
                    'Frequency of period'
                )
                ->addColumn(
                    'period_max_cycles',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'unsigned' => true],
                    'Max cycle for a period'
                )
                ->addColumn(
                    'payment_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    60,
                    [
                        'nullable' => false,
                        'default' => 'pending'
                    ],
                    'Type of payment'
                );

            $setup->getConnection()->createTable($table);

            $splitPaymentTable = $setup->getTable('hipay_split_payment');
            $salesOrderTable = $setup->getTable('sales_order');

            /**
             * Create table 'hipay_split_payment'
             */
            $table = $setup->getConnection()
                ->newTable($splitPaymentTable)
                ->addColumn(
                    'split_payment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Split Payment Id'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Order Id'
                )
                ->addColumn(
                    'real_order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['unsigned' => true, 'nullable' => false,],
                    'RealOrder Id'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Customer Id'
                )
                ->addColumn(
                    'profile_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Profile Id'
                )
                ->addColumn(
                    'card_token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false],
                    'Card Token'
                )
                ->addColumn(
                    'base_grand_total',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false],
                    'Base Grand Total'
                )
                ->addColumn(
                    'base_currency_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    3,
                    [],
                    'Base Currency Code'
                )
                ->addColumn(
                    'amount_to_pay',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false],
                    'Amount to pay'
                )
                ->addColumn(
                    'date_to_pay',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Date to pay'
                )
                ->addColumn(
                    'method_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['nullable' => false],
                    'Method code'
                )
                ->addColumn(
                    'attempts',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    4,
                    ['nullable' => false, 'unsigned' => true, 'default' => '0'],
                    'Attempts'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    60,
                    ['nullable' => false, 'default' => 'pending'],
                    'Attempts'
                )
                ->addForeignKey(
                    'fk_' . $splitPaymentTable . '_' . $salesOrderTable . '_order_id',
                    'order_id',
                    $salesOrderTable,
                    'entity_id'
                )
                ->addForeignKey(
                    'fk_hipay_split_payment_hipay_payment_profile_profile_id',
                    'profile_id',
                    $paymentProfileTable,
                    'profile_id'
                );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {

            /**
             * Create table 'hipay_cart_mapping_categories'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_cart_mapping_categories'))
                ->addColumn(
                    'mapping_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Mapping Id'
                )
                ->addColumn(
                    'category_magento_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Magento category'
                )->addColumn(
                    'category_hipay_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true, 'nullable' => true],
                    'HiPay category'
                )->addIndex(
                    $installer->getIdxName(
                        'category_magento_id_int',
                        ['category_magento_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['category_magento_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addForeignKey(
                    $installer->getFkName(
                        'hipay_cart_mapping_categories',
                        'category_magento_id',
                        'catalog_category_entity',
                        'entity_id'
                    ),
                    'category_magento_id',
                    $installer->getTable('catalog_category_entity'),
                    'entity_id'
                );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.21.0', '<')) {

            /**
             * Drop split related tables
             */
            $paymentProfileTable = $setup->getTable('hipay_payment_profile');
            $splitPaymentTable = $setup->getTable('hipay_split_payment');

            $setup->getConnection()->dropTable($splitPaymentTable);
            $setup->getConnection()->dropTable($paymentProfileTable);
        }

        $this->installShippingMappingTable($setup, $context);

        $this->installNotificationTable($setup, $context);
        $this->installHipaySalesOrderTable($setup, $context);

        $setup->endSetup();
    }

    private function installShippingMappingTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('hipay_cart_mapping_shipping');

        if ($setup->getConnection()->isTableExists($tableName)) {
            if (version_compare($context->getVersion(), '1.10.2', '<')) {
                $columns = [
                    'magento_shipping_code_custom' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Magento Shipping',
                    ],
                ];

                $this->addColumns($columns, $tableName, $setup);

                $connection = $setup->getConnection();
                $connection->dropIndex($tableName, 'MAGE_MAGENTO_SHIPPING_CODE_MAGENTO_SHIPPING_CODE');
            }
        } else {
            /**
             * Create table 'hipay_cart_mapping_shipping
             *
             * This table is use to save the mapping for the shipping
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_cart_mapping_shipping'))
                ->addColumn(
                    'mapping_shipping_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Mapping Shipping Id'
                )
                ->addColumn(
                    'magento_shipping_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Magento Shipping'
                )->addColumn(
                    'magento_shipping_code_custom',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Magento Custom Shipping'
                )->addColumn(
                    'hipay_shipping_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'HiPay Shipping'
                )->addColumn(
                    'delay_preparation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Delay preparation'
                )->addColumn(
                    'delay_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Delay delivery'
                );

            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * Create table 'hipay_customer_card'
     */
    private function installTokenTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $tableName = $setup->getTable('hipay_customer_card');

            if ($setup->getConnection()->isTableExists($tableName)) {
                $columns = [
                    'created_at' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => false,
                        'comment' => 'Creation date of token',
                    ],
                ];

                $this->addColumns($columns, $tableName, $setup);
            } else {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'card_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Card Id'
                    )
                    ->addColumn(
                        'customer_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Customer Id'
                    )
                    ->addColumn(
                        'name',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        100,
                        ['nullable' => false],
                        'Name of card'
                    )
                    ->addColumn(
                        'cc_type',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        150,
                        ['nullable' => false],
                        'Card type'
                    )
                    ->addColumn(
                        'cc_exp_month',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        2,
                        ['nullable' => false],
                        'Card expiration month'
                    )
                    ->addColumn(
                        'cc_exp_year',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        4,
                        ['nullable' => false],
                        'Card expiration year'
                    )
                    ->addColumn(
                        'cc_owner',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        100,
                        [],
                        'Card Owner'
                    )
                    ->addColumn(
                        'cc_number_enc',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        40,
                        ['nullable' => false],
                        'Card Number Encrypted'
                    )
                    ->addColumn(
                        'cc_status',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        1,
                        ['nullable' => false],
                        'Card status'
                    )
                    ->addColumn(
                        'cc_token',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '2M',
                        ['nullable' => false],
                        'HiPay token'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Creation date of token'
                    );

                $setup->getConnection()->createTable($table);
            }
        }
    }

    /**
     * Create table 'hipay_notification'
     */
    private function installNotificationTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.18.0', '<')) {
            $tableName = $setup->getTable('hipay_notification');

            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'notification_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Notification Id'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    4,
                    ['nullable' => false],
                    'HiPay status code of notification'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,],
                    'Order ID of notification'
                )
                ->addColumn(
                    'content',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'JSON content of notification'
                )
                ->addColumn(
                    'hipay_created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Creation date of notification from HiPay'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Creation date of notification'
                )->addColumn(
                    'attempts',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Attempts count'
                )->addColumn(
                    'state',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default' => \HiPay\FullserviceMagento\Model\Notification::NOTIFICATION_STATE_CREATED
                    ],
                    'State of notification'
                )
                ->addIndex(
                    $tableName,
                    ['state', 'attempts', 'status', 'created_at', 'order_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                );

            $setup->getConnection()->createTable($table);
        }
    }

    private function installHipaySalesOrderTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.26.0', '<')) {
            $tableName = $setup->getTable('hipay_sales_order');

            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order ID'
                )
                ->addColumn(
                    'is_locked',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Is Order Locked'
                )
                ->addColumn(
                    'locked_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Lock Timestamp'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )
                ->addIndex(
                    $setup->getIdxName('hipay_sales_order', ['order_id']),
                    ['order_id']
                )
                ->addForeignKey(
                    $setup->getFkName('hipay_sales_order', 'order_id', 'sales_order', 'entity_id'),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );

            $setup->getConnection()->createTable($table);
        }
    }

    private function addColumns(array $columns, string $tableName, SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($tableName, $name, $definition);
        }
    }
}
