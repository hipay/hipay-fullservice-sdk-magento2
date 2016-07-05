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

/**
 * Source model for available templates type
 */
class Templates implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getTemplates();
    }
    

    /**
     * Templates type source getter
     *
     * @return array
     */
    public function getTemplates()
    {
    	return [
    			\HiPay\Fullservice\Enum\Transaction\Template::BASIC_JS => __('Basic JS'),
    	];
    
    }
}
