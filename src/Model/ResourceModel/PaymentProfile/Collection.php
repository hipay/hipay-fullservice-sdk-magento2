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
namespace HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('HiPay\FullserviceMagento\Model\PaymentProfile', 'HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile');
    }
    
    /**
     * Get collection data as options array
     *
     * @return array
     */
    public function toOptionArray()
    {
    	return $this->_toOptionArray('profile_id');
    }
    
    /**
     * Get collection data as options hash
     *
     * @return array
     */
    public function toOptionHash()
    {
    	return $this->_toOptionHash('profile_id');
    }
    

}
