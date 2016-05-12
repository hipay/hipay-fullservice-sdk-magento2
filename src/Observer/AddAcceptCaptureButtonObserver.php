<?php
/**
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
namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * HiPay module observer
 */
class AddAcceptCaptureButtonObserver implements ObserverInterface
{
	
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry = null;


    /**
     * Constructor
     *
     */
    public function __construct(
    		\Magento\Framework\Registry $registry
    ) {
		$this->_coreRegistry = $registry;
    }

    /**
     * Add accept and capture buuton to order view toolbar
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var $block \Magento\Sales\Block\Adminhtml\Order\View */
        $block = $observer->getEvent()->getData('block');
        if($block instanceof \Magento\Sales\Block\Adminhtml\Order\View){
        	
        	if($this->getOrder()){
        		if((strpos($this->getOrder()->getPayment()->getMethod(),'hipay') !== false) 
        				&& $this->getOrder()->canReviewPayment()){
        			
        			$message = __('Are you sure you want to accept this payment?');
        			$actionUrl = $block->getUrl('hipay/order/acceptAndCapturePayment', []);
        			$block->addButton('accept_capture_payment', [
        									'label' => __('Accept and Capture Payment'),
        									'onclick' => "confirmSetLocation('{$message}', '{$actionUrl}')"
        									]);
        					
        		}
        	}
        	
        }
		
        return $this;
    }
    
    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
    	return $this->_coreRegistry->registry('sales_order');
    }
}
