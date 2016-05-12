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
namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use HiPay\Fullservice\Enum\Transaction\ECI;

class GenericConfigProvider implements ConfigProviderInterface {

	/**
	 * @var CcConfig
	 */
	protected $ccConfig;
	
	/**
	 * @var MethodInterface[]
	 */
	protected $methods = [];
	
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;
	
	/**
	 * 
	 * @var \HiPay\FullserviceMagento\Helper\Data $hipayHelper
	 */
	protected $hipayHelper;
	
	/**
	 * 
	 * @var \Magento\Checkout\Model\Session $checkoutSession
	 */
	protected $checkoutSession;
	
	/**
	 *
	 * @var \Magento\Customer\Model\Session $customerSession
	 */
	protected $customerSession;
	
	/**
	 * Card resource model
	 *
	 * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory
	 */
	protected $_collectionFactory;
	
	/**
	 * Cards collection
	 *
	 * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection
	 */
	protected $_collection;

	
	/**
	 */
	public function __construct(
			CcConfig $ccConfig,
			PaymentHelper $paymentHelper,
			\Magento\Framework\Url $urlBuilder,
			\HiPay\FullserviceMagento\Helper\Data $hipayHelper,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Customer\Model\Session $customerSession,
			\HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory $collectionFactory,
			array $methodCodes = []
			) {
		
			$this->ccConfig = $ccConfig;
			foreach ($methodCodes as $code) {
				$this->methods[$code] = $paymentHelper->getMethodInstance($code);
			}
			$this->urlBuilder = $urlBuilder;
			$this->hipayHelper = $hipayHelper;
			$this->checkoutSession = $checkoutSession;
			$this->_collectionFactory = $collectionFactory;
			$this->customerSession = $customerSession;

	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		
		$config = [];
		foreach ($this->methods as $methodCode => $method) {
			if ($method->isAvailable()) {
				$config = array_merge_recursive($config, [
						'payment' => [
								'hiPayFullservice' => [
										'afterPlaceOrderUrl' => [$methodCode => $this->urlBuilder->getUrl('hipay/payment/afterPlaceOrder',['_secure' => true])],
										'isIframeMode' => [$methodCode => $this->isIframeMode($methodCode)],
										'useOneclick' => [$methodCode => $this->useOneclick($methodCode)],
								]
						]
				]);
			}
		}
		/** @var $card \HiPay\FullserviceMagento\Model\Card */
		$cards = [];	
		foreach($this->getCustomerCards() as $card){
			$cards[] = [
					'name'=>$card->getName(),
					'ccToken'=>$card->getCcToken(),
					'ccType' => $card->getCcType()
			];
		}
		
		$config = array_merge_recursive($config, [
				'payment' => [
						'hiPayFullservice' => [
								'customerCards' => $cards,
								'selectedCard'	=> count($cards) ? current($cards)['ccToken'] : null,
								'defaultEci' => ECI::SECURE_ECOMMERCE,
								'recurringEci' => ECI::RECURRING_ECOMMERCE
						]
				]
		]);
		
		
		return $config;

	}
	
	
	/**
	 * Get cards
	 *
	 * @return bool|\HiPay\FullserviceMagento\Model\ResourceModel\Card\Collection
	 */
	protected function getCustomerCards()
	{
		if (!($customerId = $this->customerSession->getCustomerId())) {
			return false;
		}
		if (!$this->_collection) {
			$this->_collection = $this->_collectionFactory->create();
			$this->_collection
			->filterByCustomerId($customerId)
			->addOrder('card_id','desc')
			->onlyValid();
	
		}
		return $this->_collection;
	}
	
	protected function useOneclick($methodCode){
	
		$allowUseOneclick = $this->methods[$methodCode]->getConfigData('allow_use_oneclick');
		$filterOneclick = $this->methods[$methodCode]->getConfigData('filter_oneclick');
		$quote = $this->checkoutSession->getQuote();
		
		return (bool)  $this->hipayHelper->useOneclick($allowUseOneclick, $filterOneclick, $quote);
	
	}
	
	protected function isIframeMode($methodCode){
		
		return (bool) $this->methods[$methodCode]->getConfigData('iframe_mode');
		
	}

}