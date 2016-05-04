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

use \HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayManagerFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class API PaymentMethod
 * @package HiPay\FullserviceMagento\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CcMethod extends FullserviceMethod {
	
	const HIPAY_METHOD_CODE               = 'hipay_cc';
	
	
	/**
	 * @var string
	 */
	protected $_formBlockType = 'HiPay\FullserviceMagento\Block\Cc\Form';
	
	/**
	 * @var string
	 */
	protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Cc\Info';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	/**
	 * @var bool
	 */
	protected $_canSaveCc = false;
	
	/**
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleList;
	
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $_localeDate;
	
	/**
	 * Url Builder
	 *
	 * @var \Magento\Framework\Url
	 */
	protected $urlBuilder;
	
	
	/**
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
	 * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
	 * @param \Magento\Payment\Helper\Data $paymentData
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param Logger $logger
	 * @param GatewayManagerFactory $gatewayManagerFactory,
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
			\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
			\Magento\Payment\Helper\Data $paymentData,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Payment\Model\Method\Logger $logger,
			GatewayManagerFactory $gatewayManagerFactory,
			\Magento\Framework\Url $urlBuilder,
			\Magento\Framework\Module\ModuleListInterface $moduleList,
			\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
			) {
				parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $gatewayManagerFactory,$urlBuilder);
				$this->_moduleList = $moduleList;
				$this->_localeDate = $localeDate;
	}
	
	/**
	 * Assign data to info model instance
	 *
	 * @param \Magento\Framework\DataObject|mixed $data
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function assignData(\Magento\Framework\DataObject $data)
	{
		if (!$data instanceof \Magento\Framework\DataObject) {
			$data = new \Magento\Framework\DataObject($data);
		}
		$info = $this->getInfoInstance();
		$info->setCcType ( $data->getCcType () )
			->setCcOwner ( $data->getCcOwner () )
			->setCcLast4 ( substr ( $data->getCcNumber (), - 4 ) )
			->setCcNumber ( $data->getCcNumber () )
			->setCcCid ( $data->getCcCid () )
			->setCcExpMonth ( $data->getCcExpMonth () )
			->setCcExpYear ( $data->getCcExpYear () )
			->setCcSsIssue ( $data->getCcSsIssue () )
			->setCcSsStartMonth ( $data->getCcSsStartMonth () )
			->setCcSsStartYear ( $data->getCcSsStartYear () );
		
		$this->_assignAdditionalInformation($data);
		
		return $this;
	}
	
	/**
	 * Authorize payment abstract method
	 *
	 * @param \Magento\Framework\DataObject|InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @api
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		parent::authorize($payment, $amount);
		$this->place($payment);
		return $this;
	}
	
	
	/**
	 * Validate payment method information object
	 *
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function validate()
	{
		/*
		 * calling parent validate function
		 */
		parent::validate();
	
		$info = $this->getInfoInstance();
		$errorMsg = false;
		$availableTypes = explode(',', $this->getConfigData('cctypes'));
	
		$ccNumber = $info->getCcNumber();
	
		// remove credit card number delimiters such as "-" and space
		$ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
		$info->setCcNumber($ccNumber);
	
		$ccType = '';
	
		if (in_array($info->getCcType(), $availableTypes)) {
			if ($this->validateCcNum(
					$ccNumber
					) || $this->otherCcType(
							$info->getCcType()
							) && $this->validateCcNumOther(
									// Other credit card type number validation
									$ccNumber
									)
					) {
						$ccTypeRegExpList = [
								//Solo, Switch or Maestro. International safe
								'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
								'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)' .
								'|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)' .
								'|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))' .
								'|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))' .
								'|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
								// Visa
								'VI' => '/^4[0-9]{12}([0-9]{3})?$/',
								// Master Card
								'MC' => '/^5[1-5][0-9]{14}$/',
								// American Express
								'AE' => '/^3[47][0-9]{13}$/',
								// Discover
								'DI' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})' .
								'|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
								'|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
								'|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
								'|5[0-9]{14}))$/',
								// JCB
								'JCB' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}' .
								'|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
								'|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
								'|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
								'|5[0-9]{14}))$/',
						];
	
						$ccNumAndTypeMatches = isset(
								$ccTypeRegExpList[$info->getCcType()]
								) && preg_match(
										$ccTypeRegExpList[$info->getCcType()],
										$ccNumber
										);
								$ccType = $ccNumAndTypeMatches ? $info->getCcType() : 'OT';
	
								if (!$ccNumAndTypeMatches && !$this->otherCcType($info->getCcType())) {
									$errorMsg = __('The credit card number doesn\'t match the credit card type.');
								}
					} else {
						$errorMsg = __('Invalid Credit Card Number');
					}
		} else {
			$errorMsg = __('This credit card type is not allowed for this payment method.');
		}
	
		//validate credit card verification number
		if ($errorMsg === false && $this->hasVerification()) {
			$verifcationRegEx = $this->getVerificationRegEx();
			$regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
			if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
				$errorMsg = __('Please enter a valid credit card verification number.');
			}
		}
	
		if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
			$errorMsg = __('Please enter a valid credit card expiration date.');
		}
	
		if ($errorMsg) {
			throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
		}
	
		return $this;
	}
	
	/**
	 * @return bool
	 * @api
	 */
	public function hasVerification()
	{
		$configData = $this->getConfigData('useccv');
		if ($configData === null) {
			return true;
		}
		return (bool)$configData;
	}
	
	/**
	 * @return array
	 * @api
	 */
	public function getVerificationRegEx()
	{
		$verificationExpList = [
				'VI' => '/^[0-9]{3}$/',
				'MC' => '/^[0-9]{3}$/',
				'AE' => '/^[0-9]{4}$/',
				'DI' => '/^[0-9]{3}$/',
				'SS' => '/^[0-9]{3,4}$/',
				'SM' => '/^[0-9]{3,4}$/',
				'SO' => '/^[0-9]{3,4}$/',
				'OT' => '/^[0-9]{3,4}$/',
				'JCB' => '/^[0-9]{3,4}$/',
		];
		return $verificationExpList;
	}
	
	/**
	 * @param string $expYear
	 * @param string $expMonth
	 * @return bool
	 */
	protected function _validateExpDate($expYear, $expMonth)
	{
		$date = new \DateTime();
		if (!$expYear || !$expMonth || (int)$date->format('Y') > $expYear
				|| (int)$date->format('Y') == $expYear && (int)$date->format('m') > $expMonth
				) {
					return false;
				}
				return true;
	}
	
	/**
	 * @param string $type
	 * @return bool
	 * @api
	 */
	public function otherCcType($type)
	{
		return $type == 'OT';
	}
	
	/**
	 * Validate credit card number
	 *
	 * @param   string $ccNumber
	 * @return  bool
	 * @api
	 */
	public function validateCcNum($ccNumber)
	{
		$cardNumber = strrev($ccNumber);
		$numSum = 0;
	
		for ($i = 0; $i < strlen($cardNumber); $i++) {
			$currentNum = substr($cardNumber, $i, 1);
	
			/**
			 * Double every second digit
			 */
			if ($i % 2 == 1) {
				$currentNum *= 2;
			}
	
			/**
			 * Add digits of 2-digit numbers together
			 */
			if ($currentNum > 9) {
				$firstNum = $currentNum % 10;
				$secondNum = ($currentNum - $firstNum) / 10;
				$currentNum = $firstNum + $secondNum;
			}
	
			$numSum += $currentNum;
		}
	
		/**
		 * If the total has no remainder it's OK
		 */
		return $numSum % 10 == 0;
	}
	
	/**
	 * Other credit cart type number validation
	 *
	 * @param string $ccNumber
	 * @return bool
	 * @api
	 */
	public function validateCcNumOther($ccNumber)
	{
		return preg_match('/^\\d+$/', $ccNumber);
	}
	
	/**
	 * Check whether there are CC types set in configuration
	 *
	 * @param \Magento\Quote\Api\Data\CartInterface|null $quote
	 * @return bool
	 */
	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
	{
		return $this->getConfigData('cctypes', $quote ? $quote->getStoreId() : null) && parent::isAvailable($quote);
	}
	
	

	
}