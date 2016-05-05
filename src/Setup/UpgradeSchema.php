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
        
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
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
        	)      	
        	;
        
        															 
			$setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
