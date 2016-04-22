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
	 * @param string $config3dsRules
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
	
}