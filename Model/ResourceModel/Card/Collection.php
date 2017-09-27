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
namespace HiPay\FullserviceMagento\Model\ResourceModel\Card;

/**
 * Card Collection
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('HiPay\FullserviceMagento\Model\Card', 'HiPay\FullserviceMagento\Model\ResourceModel\Card');
    }

    /**
     * Filter collection by customer id
     *
     * @param int $customerId
     * @return \HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection $this
     */
    public function filterByCustomerId($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }
    
    /**
     * Return only valid cards
     * @return \HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection $this
     */
    public function onlyValid(){
    	$today = new \DateTime();
    	$currentYear = (int)$today->format('Y') ;
    	$currentMonth = (int)$today->format('m');
    	$this->addFieldToFilter('cc_exp_year', array("gteq"=>$currentYear));
    	
    	/** @var $card \HiPay\FullserviceMagento\Model\Card */
    	foreach ($this->getItems() as $card)
    	{
    		if($card->getCcExpYear() == $currentYear && $currentMonth < $card->getCcExpMonth())
    			$this->removeItemByKey($card->getId());
    	}
    	
    	return $this;
    }
}
