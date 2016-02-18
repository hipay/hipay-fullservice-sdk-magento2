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
namespace HiPay\FullserviceMagento\Model\Gateway;

/**
 * Factory class for HiPay\FullserviceMagento\Model\Gateway\Manager
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with order object
     *
     * @param \Magento\Sales\Model\Order $className
     * @return \HiPay\FullserviceMagento\Model\Gateway\Manager
     */
    public function create($order)
    {
        return $this->_objectManager->create('\HiPay\FullserviceMagento\Model\Gateway\Manager',['params'=>['order'=>$order]]);
    }
}
