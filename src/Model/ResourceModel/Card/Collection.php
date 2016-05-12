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
namespace HiPay\FullserviceMagento\Model\ResourceModel\Card;

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
