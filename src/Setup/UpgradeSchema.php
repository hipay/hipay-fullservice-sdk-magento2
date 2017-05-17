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

namespace HiPay\FullserviceMagento\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


/**
 * Upgrade Schema class
 *
 * @codeCoverageIgnore
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
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
             *
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

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            /**
             * Create table 'hipay_customer_card'
             *
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_customer_card'))
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
                );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {

            /**
             * Create table 'hipay_payment_profile'
             *
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_payment_profile'))
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
                    ['nullable' => false, 'default' => \HiPay\FullserviceMagento\Model\SplitPayment::SPLIT_PAYMENT_STATUS_PENDING],
                    'Type of payment'
                );

            $setup->getConnection()->createTable($table);

            /**
             * Create table 'hipay_split_payment'
             *
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('hipay_split_payment'))
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
                );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {

            /**
             * Create table 'hipay_cart_mapping_categories'
             *
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
                    $installer->getFkName('hipay_cart_mapping_categories', 'category_magento_id',
                        'mage_catalog_category_entity', 'entity_id'),
                    'category_magento_id',
                    $installer->getTable('mage_catalog_category_entity'),
                    'entity_id'
                );

            $setup->getConnection()->createTable($table);

            /**
             * Create table 'hipay_cart_mapping_shipping
             *
             * This table is use to save the mapping for the shipping
             *
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
                    ['nullable' => false, 'nullable' => false],
                    'Magento Shipping'
                )->addColumn(
                    'hipay_shipping_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true, 'nullable' => true],
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
                )->addIndex(
                    $installer->getIdxName(
                        'magento_shipping_code',
                        ['magento_shipping_code'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['magento_shipping_code'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
