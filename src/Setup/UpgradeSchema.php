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
        	);
        	
        	$setup->getConnection()->createTable($table);
        }
        
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
        		
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
        					['nullable' => false,'unsigned' => true],
        					'Frequency of period'
        				)
        				->addColumn(
        					'period_max_cycles',
        					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        					10,
        					['nullable' => false,'unsigned' => true],
        					'Max cycle for a period'
        				)
        				->addColumn(
        					'payment_type',
        					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        					60,
        					['nullable' => false,'default' => \HiPay\FullserviceMagento\Model\SplitPayment::SPLIT_PAYMENT_STATUS_PENDING],
        					'Type of payment'
        				)
        				;
        		
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
        					['unsigned' => true, 'nullable' => false, ],
        					'Order Id'
        				)
        				->addColumn(
        					'real_order_id',
        					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        					10,
        					['unsigned' => true, 'nullable' => false, ],
        					'RealOrder Id'
        				)
        				->addColumn(
        					'customer_id',
        					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        					null,
        					['unsigned' => true, 'nullable' => false, ],
        					'Customer Id'
        				)
        				->addColumn(
        						'profile_id',
        						\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        						null,
        						['unsigned' => true, 'nullable' => false, ],
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
        					['nullable' => false,'unsigned'=>true,'default'=>'0'],
        					'Attempts'
        				)
        				->addColumn(
        					'status',
        					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        					60,
        					['nullable' => false,'default'=>'pending'],
        					'Attempts'
        				)
        				;
        				
        				$setup->getConnection()->createTable($table);
        								
        	}	

        $setup->endSetup();
    }
}
