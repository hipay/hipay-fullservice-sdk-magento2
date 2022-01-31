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
use HiPay\FullserviceMagento\Model\Config;
use Magento\Sales\Model\Order;

/**
 * Upgrade data class
 *
 * @codeCoverageIgnore
 *
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
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer = $setup->createMigrationSetup();
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

        if (version_compare($context->getVersion(), '1.10.3', '<')) {
            $connection = $setup->getConnection();
            $newStatus = [
                'status' => Config::STATUS_CAPTURE_REFUSED,
                'label' => __('Capture refused')
            ];
            $newStatusState = [
                'status' => $newStatus['status'],
                'state' => Order::STATE_PROCESSING,
                'is_default' => 0,
                'visible_on_front' => 1
            ];

            $connection->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], [$newStatus]);
            $connection->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default', 'visible_on_front'],
                [$newStatusState]
            );
        }

        $setup->endSetup();
    }
}
