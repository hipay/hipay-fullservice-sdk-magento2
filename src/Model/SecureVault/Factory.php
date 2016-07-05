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
namespace HiPay\FullserviceMagento\Model\SecureVault;

/**
 * Factory class for HiPay\FullserviceMagento\Model\SecureVault\Manager
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
     * Create class instance with methodCode
     * Method code is used to defined Env mode (Stage or prod)
     *
     * @param string $methodCode
     * @param int|null $storeId
     * @return \HiPay\FullserviceMagento\Model\SecureVault\Manager
     */
    public function create($methodCode,$storeId=null)
    {
        return $this->_objectManager->create('\HiPay\FullserviceMagento\Model\SecureVault\Manager',['params'=>['methodCode'=>$methodCode,'storeId'=>$storeId]]);
    }
}
