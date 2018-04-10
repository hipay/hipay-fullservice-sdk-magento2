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


use HiPay\FullserviceMagento\Model\CcMethod;
use Magento\Framework\Exception\LocalizedException;
use \HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayManagerFactory;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

/**
 * Class Cc Split Payment Method
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
class CcSplitMethod extends CcMethod
{

    const HIPAY_METHOD_CODE = 'hipay_ccsplit';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory $profilefactory
     */
    protected $profileFactory;

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
        TransactionRepository $transactionRepository,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \HiPay\FullserviceMagento\Model\PaymentProfileFactory $profileFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($transactionRepository, $context, $resource, $resourceCollection, $data);

        $this->profileFactory = $profileFactory;
    }


    protected function getAddtionalInformationKeys()
    {
        return array_merge(['profile_id'], $this->_additionalInformationKeys);
    }

    protected function manualCapture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //Check if it's split payment
        //If true change captured amount
        if ($payment->getAdditionalInformation('profile_id')) {
            $profileId = $payment->getAdditionalInformation('profile_id');
            $profile = $this->getProfile($profileId);

            $amounts = $payment->getOrder()->getBaseGrandTotal();
            if ($this->_hipayConfig->useOrderCurrency()) {
                $amounts = $payment->getOrder()->getGrandTotal();
            }

            $splitAmounts = $profile->splitAmount($amounts);
            if (!is_array($splitAmounts) || !count($splitAmounts)) {
                throw new LocalizedException(__('Impossible to split the amount.'));
            }
            $firstSplit = current($splitAmounts);
            $amount = (float)$firstSplit['amountToPay'];

        }

        return parent::manualCapture($payment, $amount);


    }

    /**
     *
     * @param int $profileId
     * @throws LocalizedException
     * @return \HiPay\FullserviceMagento\Model\PaymentProfile
     */
    protected function getProfile($profileId)
    {

        if (empty($profileId)) {
            throw new LocalizedException(__('Payment Profile not found.'));
        }
        $profile = $this->profileFactory->create();
        $profile->getResource()->load($profile, $profileId);
        if (!$profile->getId()) {
            throw new LocalizedException(__('Payment Profile not found.'));
        }

        return $profile;
    }

    public function place(\Magento\Payment\Model\InfoInterface $payment)
    {

        $profileId = $payment->getAdditionalInformation('profile_id');
        $profile = $this->getProfile($profileId);

        $amounts = $payment->getOrder()->getBaseGrandTotal();
        if ($this->_hipayConfig->useOrderCurrency()) {
            $amounts = $payment->getOrder()->getGrandTotal();
        }

        $splitAmounts = $profile->splitAmount($amounts);
        if (!is_array($splitAmounts) || !count($splitAmounts)) {
            throw new LocalizedException(__('Impossible to split the amount.'));
        }
        $firstSplit = current($splitAmounts);
        $payment->getOrder()->setForcedAmount((float)$firstSplit['amountToPay']);

        return parent::place($payment);

    }

}
