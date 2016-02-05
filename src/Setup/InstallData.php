<?php
/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

namespace Hipay\FullserviceMagento\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Hipay\FullserviceMagento\Model\Config;

/**
 * @codeCoverageIgnore
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

        $data = [];
        $statuses = [
            Config::STATUS_AUTHORIZED => __('Authorized'),
            Config::STATUS_AUTHORIZED_PENDING => __('Authorized and pending'),
        	Config::STATUS_AUTHORIZATION_REQUESTED  => __('Authorization requested'),
            Config::STATUS_CAPTURE_REQUESTED  => __('Capture requested'),
        	Config::STATUS_PARTIALLY_CAPTURED  => __('Partially captured'),
        	Config::STATUS_REFUND_REQUESTED  => __('Refund requested'),
        	Config::STATUS_PARTIALLY_REFUNDED  => __('Partially refunded'),
        	Config::STATUS_AUTHENTICATION_REQUESTED  => __('Authentication requested'),
        	Config::STATUS_EXPIRED  => __('Authorization Expired'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

        /**
         * Prepare database after install
         */
        $setup->endSetup();

    }
}
