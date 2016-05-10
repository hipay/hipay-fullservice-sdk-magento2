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
 * Hipay Split Payment data model
 * 
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment getResource()
 * @method int getOrderId()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setOrderId(int $orderId)
 * @method int getRealOrderId()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setRealOrderId(int $realOrderId)
 * @method int getCustomerId()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setCustomerId(int $customerId)
 * @method string getCardToken()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setCardToken(int $cardToken)
 * @method float getBaseGrandTotal()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setBaseGrandTotal(string $baseGrandTotal) 
 * @method float getBaseCurrencyCode()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setBaseCurrencyCode(string $baseCurrencyCode) 
 * @method float getAmountToPay()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setAmountToPay(string $amountToPay) 
 * @method \DateTime getDateToPay()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setDateToPay(string $dateToPay) 
 * @method string getMethodCode()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setMethodCode(int $methodCode)
 * @method int getAttempts()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setAttempts(int $attempts)
 * @method int getStatus()
 * @method \HiPay\FullserviceMagento\Model\SplitPayment setStatus(int $status)
 * 
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SplitPayment extends \Magento\Framework\Model\AbstractModel
{

	const SPLIT_PAYMENT_STATUS_PENDING = 'pending';
	const SPLIT_PAYMENT_STATUS_FAILED = 'failed';
	const SPLIT_PAYMENT_STATUS_COMPLETE = 'complete';
	
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
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment');
        $this->setIdFieldName('split_payment_id');
    }
    
    static function getStatues()
    {
    	$statues = array(self::SPLIT_PAYMENT_STATUS_PENDING=>__('Pending'),
    			self::SPLIT_PAYMENT_STATUS_FAILED=>__('Failed'),
    			self::SPLIT_PAYMENT_STATUS_COMPLETE=>__('Complete')
    	);
    
    	return $statues;
    }
    
    /**
     *
     * @return boolean|string
     */
    /*public function pay()
    {
    
    	if(!$this->canPay())
    		Mage::throwException("This split payment is already paid!");
    
    		if(!$this->getId())
    		{
    			Mage::throwException("Split Payment not found!");
    		}
    
    		$state = $this->getMethodInstance()->paySplitPayment($this);
    
    		switch ($state)
    		{
    			case Allopass_Hipay_Model_Method_Abstract::STATE_COMPLETED:
    			case Allopass_Hipay_Model_Method_Abstract::STATE_FORWARDING:
    			case Allopass_Hipay_Model_Method_Abstract::STATE_PENDING:
    				$this->setStatus(self::SPLIT_PAYMENT_STATUS_COMPLETE);
    				break;
    			case Allopass_Hipay_Model_Method_Abstract::STATE_DECLINED:
    			case Allopass_Hipay_Model_Method_Abstract::STATE_ERROR:
    			default:
    				$this->setStatus(self::SPLIT_PAYMENT_STATUS_FAILED);
    				$this->sendErrorEmail();
    				break;
    					
    		}
    
    		$this->setAttempts($this->getAttempts() + 1);
    		$this->save();
    		return $this;
    
    }
    
    public function sendErrorEmail()
    {
    	
    	$helperCheckout = Mage::helper('checkout');
    	$order = Mage::getModel('sales/order')->load($this->getOrderId());
    	$message = Mage::helper('hipay')->__("Error on request split Payment HIPAY. Split Payment Id: ".$this->getSplitPaymentId());
    	$helperCheckout->sendPaymentFailedEmail($order, $message,'Split Payment Hipay');
    }*/
    
    /**
     * @return Allopass_Hipay_Model_Method_Abstract
     */
  /*  public function getMethodInstance()
    {
    	list($moduleName,$methodClass) = explode("_",$this->getMethodCode());
    	//Fix bug due to upper letter in class name
    	if(strpos($methodClass,'xtimes') !== false)
    	{
    		$methodClass = str_replace("x","X",$methodClass);
    	}
    	return Mage::getSingleton($moduleName . "/method_" . $methodClass );
    }
    
    public function canPay()
    {
    	return $this->getStatus() == self::SPLIT_PAYMENT_STATUS_FAILED || $this->getStatus() == self::SPLIT_PAYMENT_STATUS_PENDING;
    }
	*/
}
