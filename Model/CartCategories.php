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

use Magento\Framework\Exception\LocalizedException;

/**
 * Hipay Cart categories data model
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartCategories extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Init resource model and id field
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\HiPay\FullserviceMagento\Model\ResourceModel\CartCategories::class);
        $this->setIdFieldName('mapping_id');
    }
}
