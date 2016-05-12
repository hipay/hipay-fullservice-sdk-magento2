<?php
/*
 * HiPay fullservice Magento2
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
namespace HiPay\FullserviceMagento\Cron;

use HiPay\FullserviceMagento\Cron;
use HiPay\FullserviceMagento\Model\SplitPayment;

/**
 * FullserviceMagento event observer
 * Pay split payments in pending
 */
class PaySplitPayment
{

	const MAX_ATTEMPTS = 3;
	
	/**
	 * 
	 * @var \Psr\Log\LoggerInterface $logger
	 */
	protected $logger;
	
	/**
	 *
	 * @var \HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory
	 */
	protected $spFactory;
	
    public function __construct(
    		\HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory,
    		\Psr\Log\LoggerInterface $logger
    ) {
        $this->spFactory = $spFactory;
        $this->logger = $logger;
    }

    /**
     * Cron job method to pay split payments in pending
     *
     * @return void
     */
    public function execute()
    {
        $date = new \DateTime();
		
        /** @var $splitPayments \HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment\Collection */
		$splitPayments =  $this->spFactory->create()->getCollection()
								->addFieldToFilter('status',array('in'=>array(SplitPayment::SPLIT_PAYMENT_STATUS_PENDING,
																			SplitPayment::SPLIT_PAYMENT_STATUS_FAILED)))
								->addFieldTofilter('attempts',array('lt'=>3))
								->addFieldTofilter('date_to_pay',array('to' => $date->format('Y-m-d 00:00:00')));

		foreach ($splitPayments as $splitPayment) {
			try {
				$splitPayment->pay();
			} catch (Exception $e) {
				$this->logger->debug($e->getMessage());
			}
		}
    }
}
