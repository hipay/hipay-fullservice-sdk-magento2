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

namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Payment profile data model
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile getResource()
 * @method string getName()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setName(string $name)
 * @method string getPeriodUnit()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodUnit(string $periodUnit)
 * @method int getPeriodFrequency()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodFrequency(string $periodFrequency)
 * @method int getPeriodMaxCycles()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPeriodMaxCycles(string $periodMaxCycles)
 * @method string getPaymentType()
 * @method \HiPay\FullserviceMagento\Model\PaymentProfile setPaymentType(string $paymentType)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentProfile extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Period units
     *
     * @var string
     */
    const PERIOD_UNIT_DAY = 'day';
    const PERIOD_UNIT_WEEK = 'week';
    const PERIOD_UNIT_SEMI_MONTH = 'semi_month';
    const PERIOD_UNIT_MONTH = 'month';
    const PERIOD_UNIT_YEAR = 'year';

    /**
     * Payment types
     */
    const PAYMENT_TYPE_SPLIT = '\HiPay\FullserviceMagento\Model\SplitPayment';
    const PAYMENT_TYPE_RECURRING = 'recurring_payment';

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\PaymentProfile\Type\Factory $typeFactory
     */
    protected $typeFactory;

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
        \HiPay\FullserviceMagento\Model\PaymentProfile\Type\Factory $typeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->typeFactory = $typeFactory;
    }


    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile');
        $this->setIdFieldName('profile_id');
    }

    /**
     * Split an amount by profile data
     * @param float $amount
     * @return []
     */
    public function splitAmount($amount)
    {
        $paymentsSplit = array();

        $maxCycles = ( int )$this->getPeriodMaxCycles();

        $periodFrequency = ( int )$this->getPeriodFrequency();
        $periodUnit = $this->getPeriodUnit();

        $todayDate = new \DateTime ();

        $part = ( int )($amount / $maxCycles);
        $fmod = fmod($amount, $maxCycles);

        for ($i = 0; $i <= ($maxCycles - 1); $i++) {
            $j = $i - 1;
            $todayClone = clone $todayDate;
            $frequencyValue = $periodFrequency + $j;
            switch ($periodUnit) {
                case self::PERIOD_UNIT_MONTH : {
                    $interval = new \DateInterval ("P{$frequencyValue}M");
                    $dateToPay = $todayClone->add($interval)->format("Y-m-d");
                    break;
                }
                case self::PERIOD_UNIT_DAY : {
                    $interval = new \DateInterval ("P{$frequencyValue}D");
                    $dateToPay = $todayClone->add($interval)->format("Y-m-d");
                    break;
                }
                case self::PERIOD_UNIT_SEMI_MONTH : {
                    $semiMonthFreq = 15 + $frequencyValue;
                    $interval = new \DateInterval ("P{$semiMonthFreq}D");
                    $dateToPay = $todayClone->add($interval)->format("Y-m-d");
                    break;
                }
                case self::PERIOD_UNIT_WEEK : {
                    $week = 7 + $frequencyValue;
                    $interval = new \DateInterval ("P{$week}D");
                    $dateToPay = $todayClone->add($interval)->format("Y-m-d");
                    break;
                }
                case self::PERIOD_UNIT_YEAR : {
                    $interval = new \DateInterval ("P{$frequencyValue}Y");
                    $dateToPay = $todayClone->add($interval)->format("Y-m-d");
                    break;
                }
            }

            $amountToPay = $i == 0 ? ($part + $fmod) : $part;

            $paymentsSplit [] = [
                'dateToPay' => $dateToPay,
                'amountToPay' => $amountToPay
            ];
        }

        return $paymentsSplit;

    }

    public function getAllPaymentTypes($withLabels = true)
    {
        $paymenTypes = [
            self::PAYMENT_TYPE_SPLIT,
            self::PAYMENT_TYPE_RECURRING,
        ];

        if ($withLabels) {
            $result = [];
            foreach ($paymenTypes as $paymenType) {
                $result[$paymenType] = $this->getPaymentTypeLabel($paymenType);
            }
            return $result;
        }
        return $paymenTypes;
    }

    public function getPaymentTypeLabel($paymentType)
    {
        switch ($paymentType) {
            case self::PAYMENT_TYPE_SPLIT:
                return __('Split payment');
            case self::PAYMENT_TYPE_RECURRING:
                return __('Recurring Payment');
        }
        return $paymentType;
    }

}
