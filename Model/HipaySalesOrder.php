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

namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Sales Order
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HipaySalesOrder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        $this->_init(\HiPay\FullserviceMagento\Model\ResourceModel\HipaySalesOrder::class);
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return (bool)$this->getData('is_locked');
    }

    /**
     * @param bool $isLocked
     * @return $this
     */
    public function setIsLocked($isLocked)
    {
        return $this->setData('is_locked', $isLocked);
    }
}
