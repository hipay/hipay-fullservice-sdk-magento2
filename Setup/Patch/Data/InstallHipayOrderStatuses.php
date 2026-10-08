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

use HiPay\FullserviceMagento\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;

/**
 * Install HiPay custom order statuses and their state mappings
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class InstallHipayOrderStatuses implements DataPatchInterface
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
     * HiPay statuses with their default order state
     *
     * @return array
     */
    private function getStatuses()
    {
        return [
            Config::STATUS_AUTHORIZED => ['label' => 'Authorized', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_AUTHORIZED_PENDING => [
                'label' => 'Authorized and pending',
                'state' => Order::STATE_PAYMENT_REVIEW,
            ],
            Config::STATUS_AUTHORIZATION_REQUESTED => [
                'label' => 'Authorization requested',
                'state' => Order::STATE_PENDING_PAYMENT,
            ],
            Config::STATUS_CAPTURE_REQUESTED => ['label' => 'Capture requested', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_CAPTURE_REFUSED => ['label' => 'Capture refused', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_PARTIALLY_CAPTURED => ['label' => 'Partially captured', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUND_REQUESTED => ['label' => 'Refund requested', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUNDED => ['label' => 'Refunded', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUND_REFUSED => ['label' => 'Refund refused', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_PARTIALLY_REFUNDED => ['label' => 'Partially refunded', 'state' => Order::STATE_PROCESSING],
            Config::STATUS_AUTHENTICATION_REQUESTED => [
                'label' => 'Authentication requested',
                'state' => Order::STATE_PENDING_PAYMENT,
            ],
            Config::STATUS_EXPIRED => ['label' => 'Authorization Expired', 'state' => Order::STATE_HOLDED],
        ];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $statusTable = $this->moduleDataSetup->getTable('sales_order_status');
        $stateTable = $this->moduleDataSetup->getTable('sales_order_status_state');

        $statusRows = [];
        $stateRows = [];
        foreach ($this->getStatuses() as $code => $info) {
            $statusRows[] = ['status' => $code, 'label' => $info['label']];
            $stateRows[] = [
                'status' => $code,
                'state' => $info['state'],
                'is_default' => 0,
                'visible_on_front' => 1,
            ];
        }

        // Idempotent: keep existing labels/mappings on installs that already have them.
        $connection->insertOnDuplicate($statusTable, $statusRows, ['label']);
        $connection->insertOnDuplicate(
            $stateTable,
            $stateRows,
            ['state', 'is_default', 'visible_on_front']
        );

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
