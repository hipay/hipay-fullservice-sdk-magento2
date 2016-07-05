<?php
/*
 * HiPay fullservice SDK
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

namespace HiPay\FullserviceMagento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper{
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Model\RuleFactory $_ruleFactory
	 */
	protected $_ruleFactory;
	
	public function __construct(
			Context $context,
			\HiPay\FullserviceMagento\Model\RuleFactory $ruleFactory
	){
		parent::__construct($context);
		$this->_ruleFactory = $ruleFactory;
	}
	
	/**
	 * 
	 * @param bool $use3dSecure
	 * @param int $config3dsRules
	 * @param \Magento\Quote\Model\Quote $quote
	 */
	public function is3dSecure($use3dSecure, $config3dsRules, $quote = null)
	{
		$params = 0;
		if($use3dSecure > 0 && is_null($quote)){
			$params = 1;
		}else{
			switch ((int)$use3dSecure) {
				case 1:
					$params = 1;
					break;
				case 2:
				case 3:
					/* @var $rule Allopass_Hipay_Model_Rule */				
					$rule = $this->_ruleFactory->create()->load($config3dsRules);
					if($rule->getId() && $rule->validate($quote) )
					{
						$params = 1;
						if((int)$use3dSecure == 3)//case for force 3ds if rules are validated
							$params = 2;
	
					}
					break;
				case 4:
					$params = 2;
					break;
			}
		}
		return $params;
	}
	/**
	 * 
	 * @param bool $allowUseOneclick Method config Data
	 * @param int $filterOneclick Rule's id in configuration
	 * @param \Magento\Quote\Model\Quote $quote
	 */
	public function useOneclick($allowUseOneclick,$filterOneclick,$quote){
		switch ((int)$allowUseOneclick) {
			case 0:
				return false; 
			case 1:
				/* @var $rule Allopass_Hipay_Model_Rule */	 
				$rule = $this->_ruleFactory->create()->load($filterOneclick);
				if($rule->getId())
				{
					return (int)$rule->validate($quote);
				}
				return true;		 
		}	
	}
	
}