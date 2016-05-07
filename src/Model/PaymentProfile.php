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
namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Payment profile data model
 * 
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile getResource()
 * @method string getName()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setName(string $name)
 * @method string getPeriodUnit()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodUnit(string $periodUnit)  
 * @method int getPeriodFrequency()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodFrequency(string $periodFrequency)  
 * @method int getPeriodMaxCycles()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodMaxCycles(string $periodMaxCycles) 
 * @method string getPaymentType()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPaymentType(string $paymentType)  
 * 
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentProfile extends \Magento\Framework\Model\AbstractModel
{

	/**
	 * Period units
	 *
	 * @var string
	 */
	const PERIOD_UNIT_DAY = 'day';
	const PERIOD_UNIT_WEEK = 'week';
	const PERIOD_UNIT_SEMI_MONTH = 'semi_month';
	const PERIOD_UNIT_MONTH = 'month';
	const PERIOD_UNIT_YEAR = 'year';
	
	/**
	 * Payment types
	 */
	const PAYMENT_TYPE_SPLIT = 'split_payment';
	const PAYMENT_TYPE_RECURRING = 'recurring_payment';
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
			) {

				parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}
	
	
  /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile');
        $this->setIdFieldName('profile_id');
    }
    
    public function getAllPaymentTypes($withLabels = true)
    {
    	$paymenTypes = [
    			self::PAYMENT_TYPE_SPLIT,
    			self::PAYMENT_TYPE_RECURRING,
    	];
    
    	if ($withLabels) {
    		$result = [];
    		foreach ($paymenTypes as $paymenType) {
    			$result[$paymenType] = $this->getPaymentTypeLabel($paymenType);
    		}
    		return $result;
    	}
    	return $paymenTypes;
    }
    
    public function getPaymentTypeLabel($paymentType)
    {
    	switch ($paymentType)
    	{
    		case self::PAYMENT_TYPE_SPLIT:  return __('Split payment');
    		case self::PAYMENT_TYPE_RECURRING: return __('Recurring Payment');
    	}
    	return $paymentType;
    }
    
    /**
     * Getter for field label
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldLabel($field)
    {
    	switch ($field) {
    		case 'subscriber_name':
    			return __('Subscriber Name');
    		case 'start_datetime':
    			return __('Start Date');
    		case 'internal_reference_id':
    			return __('Internal Reference ID');
    		case 'schedule_description':
    			return __('Schedule Description');
    		case 'suspension_threshold':
    			return __('Maximum Payment Failures');
    		case 'bill_failed_later':
    			return __('Auto Bill on Next Cycle');
    		case 'period_unit':
    			return __('Billing Period Unit');
    		case 'period_frequency':
    			return __('Billing Frequency');
    		case 'period_max_cycles':
    			return __('Maximum Billing Cycles');
    		case 'billing_amount':
    			return __('Billing Amount');
    		case 'trial_period_unit':
    			return __('Trial Billing Period Unit');
    		case 'trial_period_frequency':
    			return __('Trial Billing Frequency');
    		case 'trial_period_max_cycles':
    			return __('Maximum Trial Billing Cycles');
    		case 'trial_billing_amount':
    			return __('Trial Billing Amount');
    		case 'currency_code':
    			return __('Currency');
    		case 'shipping_amount':
    			return __('Shipping Amount');
    		case 'tax_amount':
    			return __('Tax Amount');
    		case 'init_amount':
    			return __('Initial Fee');
    		case 'init_may_fail':
    			return __('Allow Initial Fee Failure');
    		case 'method_code':
    			return __('Payment Method');
    		case 'reference_id':
    			return __('Payment Reference ID');
    	}
    }
    
	
}
