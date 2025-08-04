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
 * @copyright Copyright (c) 2025 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Backend model to validate the cancellation delay value (in minutes).
 *
 * @author    Ibrahim Amara <iamara.ext@hipay.com>
 * @copyright Copyright (c) 2025 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class CancellationDelay extends Value
{
    const MIN_VALUE = 30;
    const MAX_VALUE = 10080;

    /**
     * Validate the cancellation delay value.
     *
     * @throws ValidatorException
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!is_numeric($value)) {
            throw new ValidatorException(__('Only numeric values are allowed for cancellation delay.'));
        }

        $intValue = (int) $value;

        if ($intValue < self::MIN_VALUE) {
            throw new ValidatorException(__('Minimum allowed value for cancellation delay is %1 minutes.', self::MIN_VALUE));
        }

        if ($intValue > self::MAX_VALUE) {
            throw new ValidatorException(__('Maximum allowed value for cancellation delay is %1 minutes (7 days).', self::MAX_VALUE));
        }

        $this->setValue($intValue);
        return parent::beforeSave();
    }
}
