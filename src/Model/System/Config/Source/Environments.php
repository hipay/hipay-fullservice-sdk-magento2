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
namespace HiPay\FullserviceMagento\Model\System\Config\Source;

use HiPay\Fullservice\HTTP\Configuration\Configuration as ConfigSDK;

/**
 * Source model for available environments
 */
class Environments implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getEnvironments();
    }
    
    /**
     * Environments source getter
     *
     * @return array
     */
    public function getEnvironments()
    {
    	$envs = [
    			ConfigSDK::API_ENV_STAGE => __('Stage'),
    			ConfigSDK::API_ENV_PRODUCTION => __('Production'),
    	];
    
    	return $envs;
    }
}
