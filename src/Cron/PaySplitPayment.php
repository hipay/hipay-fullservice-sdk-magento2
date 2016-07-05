<?php
/**
 * HiPay fullservice Magento
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
namespace HiPay\FullserviceMagento\Cron;

use HiPay\FullserviceMagento\Cron;
use HiPay\FullserviceMagento\Model\SplitPayment;

/**
 * HiPay module crontab
 * Used to pay all split payments in pending with day frequency
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
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
