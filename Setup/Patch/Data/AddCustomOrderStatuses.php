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
 * Add all HiPay custom order statuses, including "Capture Refused".
 */
class AddCustomOrderStatuses implements DataPatchInterface
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
     * Apply patch to create HiPay custom order statuses.
     *
     * @return void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $statusTable = $this->moduleDataSetup->getTable('sales_order_status');
        $stateTable = $this->moduleDataSetup->getTable('sales_order_status_state');

        $statuses = [
            Config::STATUS_AUTHORIZED => ['label' => __('Authorized'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_AUTHORIZED_PENDING => [
                'label' => __('Authorized and pending'),
                'state' => Order::STATE_PAYMENT_REVIEW
            ],
            Config::STATUS_AUTHORIZATION_REQUESTED => [
                'label' => __('Authorization requested'),
                'state' => Order::STATE_PENDING_PAYMENT
            ],
            Config::STATUS_CAPTURE_REQUESTED => [
                'label' => __('Capture requested'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_CAPTURE_REFUSED => [ // 🆕 ajouté ici
                'label' => __('Capture refused'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_PARTIALLY_CAPTURED => [
                'label' => __('Partially captured'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_REFUND_REQUESTED => [
                'label' => __('Refund requested'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_REFUNDED => ['label' => __('Refunded'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUND_REFUSED => ['label' => __('Refund refused'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_PARTIALLY_REFUNDED => [
                'label' => __('Partially refunded'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_AUTHENTICATION_REQUESTED => [
                'label' => __('Authentication requested'),
                'state' => Order::STATE_PENDING_PAYMENT
            ],
            Config::STATUS_EXPIRED => ['label' => __('Authorization expired'), 'state' => Order::STATE_HOLDED],
        ];

        foreach ($statuses as $code => $info) {
            $exists = (bool)$connection->fetchOne(
                $connection->select()
                    ->from($statusTable, ['status'])
                    ->where('status = ?', $code)
            );

            if (!$exists) {
                $connection->insert($statusTable, [
                    'status' => $code,
                    'label' => $info['label']
                ]);

                $connection->insert($stateTable, [
                    'status' => $code,
                    'state' => $info['state'],
                    'is_default' => isset($info['default']) ? 1 : 0,
                    'visible_on_front' => 1
                ]);
            }
        }

        $connection->endSetup();
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
