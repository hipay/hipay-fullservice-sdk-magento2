<?php
/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace Hipay\FSM2\Model;

use Magento\Payment\Model\Method\Online\GatewayInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\ConfigInterface;


/**
 * Class PaymentMethod
 * @package Hipay\FSM2\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedMethod extends AbstractMethod implements GatewayInterface {
	
	const HIPAY_HOSTED_METHOD_CODE               = 'hipay_hosted';
	
	/**
	 * @var string
	 */
	protected $_formBlockType = 'Hipay\FSM2\Block\Hosted\Form';
	
	/**
	 * @var string
	 */
	protected $_infoBlockType = 'Hipay\FSM2\Block\Hosted\Info';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_HOSTED_METHOD_CODE;
	
	/**
	 * Fields that should be replaced in debug with '***'
	 *
	 * @var array
	 */
	protected $_debugReplacePrivateDataKeys = [];
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_isGateway = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canAuthorize = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canCapture = true;
	
	
	/**
	 * 
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
	 * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
	 * @param \Magento\Payment\Helper\Data $paymentData
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Payment\Model\Method\Logger $logger
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct( 
		\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []){
		
		parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger);
	}
	
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Magento\Payment\Model\Method\Online\GatewayInterface::postRequest()
	 */
	public function postRequest(DataObject $request, ConfigInterface $config) {
		// TODO Auto-generated method stub
	}
}