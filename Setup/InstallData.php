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

namespace HiPay\FullserviceMagento\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use HiPay\FullserviceMagento\Model\Config;
use Magento\Sales\Model\Order;

/**
 * Install data class
 *
 * @codeCoverageIgnore
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Prepare database for install
         */
        $setup->startSetup();

        $statuesData = [];
        $statuesToStateData = [];
        $statuses = [
            Config::STATUS_AUTHORIZED => ["label" => __('Authorized'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_AUTHORIZED_PENDING => [
                "label" => __('Authorized and pending'),
                'state' => Order::STATE_PAYMENT_REVIEW
            ],
            Config::STATUS_AUTHORIZATION_REQUESTED => [
                "label" => __('Authorization requested'),
                'state' => Order::STATE_PENDING_PAYMENT
            ],
            Config::STATUS_CAPTURE_REQUESTED => [
                "label" => __('Capture requested'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_PARTIALLY_CAPTURED => [
                "label" => __('Partially captured'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_REFUND_REQUESTED => ["label" => __('Refund requested'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUNDED => ["label" => __('Refunded'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_REFUND_REFUSED => ["label" => __('Refund refused'), 'state' => Order::STATE_PROCESSING],
            Config::STATUS_PARTIALLY_REFUNDED => [
                "label" => __('Partially refunded'),
                'state' => Order::STATE_PROCESSING
            ],
            Config::STATUS_AUTHENTICATION_REQUESTED => [
                "label" => __('Authentication requested'),
                'state' => Order::STATE_PENDING_PAYMENT
            ],
            Config::STATUS_EXPIRED => ["label" => __('Authorization Expired'), 'state' => Order::STATE_HOLDED],
        ];
        foreach ($statuses as $code => $info) {
            $statuesData[] = ['status' => $code, 'label' => $info['label']];
            $statuesToStateData[] = [
                'status' => $code,
                'state' => $info['state'],
                'is_default' => isset($info['default']) ? 1 : 0,
                'visible_on_front' => 1
            ];
        }
        //Insert new statues
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $statuesData);

        //Assign new statues to states
        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $statuesToStateData
        );

        /**
         * Prepare database after install
         */
        $setup->endSetup();
    }
}
