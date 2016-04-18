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

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
	
	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
		
		$installer =  $setup->createMigrationSetup();
		$setup->startSetup();
		if (version_compare($context->getVersion(), '2.0.1', '<')) {
			$installer->appendClassAliasReplace(
					'hipay_rule',
					'conditions_serialized',
					\Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
					\Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
					['rule_id']
					);
			$installer->appendClassAliasReplace(
					'hipay_rule',
					'actions_serialized',
					\Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
					\Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
					['rule_id']
					);
			
			$installer->doUpdateClassAliases();
		}
		
		$setup->endSetup();
	}
	
}
