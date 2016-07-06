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

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Upgrade data class
 * 
 * @codeCoverageIgnore
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class UpgradeData implements UpgradeDataInterface
{
	
	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
		
		$setup->startSetup();
		if (version_compare($context->getVersion(), '2.0.1') < 0) {
			$installer =  $setup->createMigrationSetup();
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
