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
namespace HiPay\FullserviceMagento\Model\System\Config\Backend;



class Sortable extends \Magento\Framework\App\Config\Value {
	
	/**
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param ScopeConfigInterface $config
	 * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\App\Config\ScopeConfigInterface $config,
			\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
			) {
				parent::__construct($context, $registry,$config,$cacheTypeList, $resource, $resourceCollection, $data);
	}
	
	/**
	 * Processing object before save data
	 *
	 * @return $this
	 */
	public function beforeSave()
	{
		return parent::beforeSave();
	}
	
	protected function _afterload()
	{
		if(!is_array($this->getValue())){			
			$this->setValue(explode(",", $this->getValue()));
		}
		return parent::_afterload();
	}
	
	
}