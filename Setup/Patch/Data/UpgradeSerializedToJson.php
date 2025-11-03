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

namespace HiPay\FullserviceMagento\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Module\Setup\Migration;

/**
 * Migrate serialized fields to JSON format for HiPay rules.
 */
class UpgradeSerializedToJson implements DataPatchInterface
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
     * Apply patch to migrate serialized fields to JSON if necessary.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $ruleTable = $this->moduleDataSetup->getTable('hipay_rule');

        // Detect if migration is needed (old serialized data still present)
        $needsMigration = (bool)$connection->fetchOne(
            $connection->select()
                ->from($ruleTable, ['conditions_serialized'])
                ->where('conditions_serialized REGEXP ?', '^((s|i|d|b|a|O|C):|N;)')
                ->orWhere('actions_serialized REGEXP ?', '^((s|i|d|b|a|O|C):|N;)')
        );

        if ($needsMigration) {
            $migration = $this->moduleDataSetup->createMigrationSetup();

            $migration->appendClassAliasReplace(
                'hipay_rule',
                'conditions_serialized',
                Migration::ENTITY_TYPE_MODEL,
                Migration::FIELD_CONTENT_TYPE_SERIALIZED,
                ['rule_id']
            );

            $migration->appendClassAliasReplace(
                'hipay_rule',
                'actions_serialized',
                Migration::ENTITY_TYPE_MODEL,
                Migration::FIELD_CONTENT_TYPE_SERIALIZED,
                ['rule_id']
            );

            $migration->doUpdateClassAliases();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
