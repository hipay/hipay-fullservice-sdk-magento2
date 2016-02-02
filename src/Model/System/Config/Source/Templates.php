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
namespace Hipay\FullserviceMagento\Model\System\Config\Source;

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
    			\Hipay\Fullservice\Enum\Transaction\Template::BASIC_JS => __('Basic JS'),
    	];
    
    }
}
