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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\Payment;

use Magento\Framework\Controller\ResultFactory;
/**
 *
 * @author kassim
 *        
 */
class Profile extends \Magento\Backend\App\Action {
	
	/**
	 * Payment profiles Grid
	 * {@inheritDoc}
	 * @see \Magento\Framework\App\ActionInterface::execute()
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
    public function execute()
    {
    	$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    	$resultPage->setActiveMenu('HiPay_FullserviceMagento::hipay_payment_profile');
    	$resultPage->addBreadcrumb(__('HiPay'), __('HiPay'));
    	$resultPage->addBreadcrumb(__('Payment Profiles'), __('Payment Profiles'));
    	
    	$resultPage->getConfig()->getTitle()->prepend(__('Payment Profiles'));
    	
    	
    	return $resultPage;
    }
	
}