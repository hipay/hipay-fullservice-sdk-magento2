<?php
/**
 * HiPay Fullservice Magento
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

namespace HiPay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\FullserviceMethod;
use \HiPay\FullserviceMagento\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;
use \HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayManagerFactory;
use Zend\Validator;
use Magento\Directory\Model;

/**
 * SDD Method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Sdd extends FullserviceMethod
{
    const HIPAY_METHOD_CODE = 'hipay_sdd';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * Payment Method feature Refund
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * Payment Method feature Refund Invoice Partial
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     *  Additional datas
     *
     * @var array
     */
    protected $_additionalInformationKeys = [
        'sdd_gender',
        'sdd_bank_name',
        'sdd_code_bic',
        'sdd_iban',
        'sdd_firstname',
        'sdd_lastname',
        'cc_type'
    ];


    /**
     *
     * @param \HiPay\FullserviceMagento\Model\Method\Context $context
     * @param \HiPay\FullserviceMagento\Model\PaymentProfileFactory $profileFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \HiPay\FullserviceMagento\Model\PaymentProfileFactory $profileFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $resource, $resourceCollection, $data);

        if ($this->getConfigData('electronic_signature')) {
            $this->setIsInitializeNeeded(true);
        }
    }


    protected function getAddtionalInformationKeys()
    {
        return array_merge(['profile_id'], $this->_additionalInformationKeys);
    }

    public function place(\Magento\Payment\Model\InfoInterface $payment)
    {
        return parent::place($payment);
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _assignAdditionalInformation(\Magento\Framework\DataObject $additionalData)
    {
        parent::_assignAdditionalInformation($additionalData);
        $info = $this->getInfoInstance();
        $info->setCcType($additionalData->getCcType());

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        /*
        * calling parent validate function
        */
        parent::validate();
        $info = $this->getInfoInstance();

        if (!$this->getConfigData('electronic_signature')) {
            $errorMsg = '';

            // Get iso code from order or quote ( Validate is called twice per magento core )
            $order = $info->getQuote();
            if ($info->getOrder()) {
                $order = $info->getOrder();
            }

            // Instantiate validators for the model
            $validatorIban = new \Zend\Validator\Iban(array('country_code' => $order->getBillingAddress()->getCountryId()));
            $validatorEmpty = new \Zend\Validator\NotEmpty();

            if (!$validatorIban->isValid($info->getAdditionalInformation('sdd_iban'))) {
                $errorMsg = __('Iban is not correct, please enter a valid Iban.');
            } else {
                if (!$validatorEmpty->isValid($info->getAdditionalInformation('sdd_firstname'))) {
                    $errorMsg = __('Firstname is mandatory.');
                } elseif (!$validatorEmpty->isValid($info->getAdditionalInformation('sdd_lastname'))) {
                    $errorMsg = _('Lastname is mandatory.');
                } elseif (!$validatorEmpty->isValid($info->getAdditionalInformation('sdd_code_bic'))) {
                    $errorMsg = __('Code BIC is not correct, please enter a valid Code BIC.');
                } elseif (!$validatorEmpty->isValid($info->getAdditionalInformation('sdd_bank_name'))) {
                    $errorMsg = __('Bank name is not correct, please enter a valid Bank name.');
                }
            }

            if ($errorMsg) {
                throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
            }
        }
        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {

        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

        $this->_setHostedUrl($order);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);

    }

    protected function _setHostedUrl(\Magento\Sales\Model\Order $order)
    {
        $gateway = $this->_gatewayManagerFactory->create($order);

        //Call fullservice api to get hosted page url
        $hppModel = $gateway->requestNewOrder();
        $order->getPayment()->setAdditionalInformation('redirectUrl', $hppModel->getForwardUrl());
    }

    /**
     * Set initialization requirement state
     *
     * @param bool $isInitializeNeeded
     * @return void
     */
    public function setIsInitializeNeeded($isInitializeNeeded = true)
    {
        $this->_isInitializeNeeded = (bool)$isInitializeNeeded;
    }

}
