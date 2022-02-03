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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\System\Config\Source;

use HiPay\FullserviceMagento\Model\PaymentProfile as PaymentProfileModel;

/**
 * Source model for period unit
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PeriodUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getAllPeriodUnits() as $unit => $label) {
            $options[] = array('value' => $unit, 'label' => $label);
        }

        return $options;
    }

    /**
     * Getter for available period units
     *
     * @param  bool $withLabels
     * @return array
     */
    public function getAllPeriodUnits($withLabels = true)
    {
        $units = [
            PaymentProfileModel::PERIOD_UNIT_DAY,
            PaymentProfileModel::PERIOD_UNIT_WEEK,
            PaymentProfileModel::PERIOD_UNIT_SEMI_MONTH,
            PaymentProfileModel::PERIOD_UNIT_MONTH,
            PaymentProfileModel::PERIOD_UNIT_YEAR
        ];

        if ($withLabels) {
            $result = [];
            foreach ($units as $unit) {
                $result[$unit] = $this->getPeriodUnitLabel($unit);
            }
            return $result;
        }
        return $units;
    }

    /**
     * Render label for specified period unit
     *
     * @param  $unit
     * @return \Magento\Framework\Phrase
     */
    public function getPeriodUnitLabel($unit)
    {
        switch ($unit) {
            case PaymentProfileModel::PERIOD_UNIT_DAY:
                return __('Day');
            case PaymentProfileModel::PERIOD_UNIT_WEEK:
                return __('Week');
            case PaymentProfileModel::PERIOD_UNIT_SEMI_MONTH:
                return __('Two Weeks');
            case PaymentProfileModel::PERIOD_UNIT_MONTH:
                return __('Month');
            case PaymentProfileModel::PERIOD_UNIT_YEAR:
                return __('Year');
        }
        return $unit;
    }
}
