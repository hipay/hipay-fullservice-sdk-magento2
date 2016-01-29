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
 * Source model for available payment actions
 */
class PaymentActions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Hipay\FullserviceMagento\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @param \Hipay\FullserviceMagento\Model\Config\Factory $configFactory
     */
    public function __construct(\Hipay\FullserviceMagento\Model\Config\Factory $configFactory)
    {
        $this->_configFactory = $configFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_configFactory->create('\Hipay\FullserviceMagento\Model\Config')->getPaymentActions();
    }
}
