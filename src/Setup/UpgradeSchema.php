<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

namespace HiPay\FullserviceMagento\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
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

        $setup->endSetup();
    }
}
