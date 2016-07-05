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
