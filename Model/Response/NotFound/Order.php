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

namespace HiPay\FullserviceMagento\Model\Response\NotFound;

use HiPay\FullserviceMagento\Model\ResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * ResponseNotFoundOrder Model
 *
 * @copyright Copyright (c) 2025 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Order extends AbstractModel
{
    public const FIELD_ENTITY_ID = 'entity_id';
    public const FIELD_ORDER_ID  = 'order_id';
    public const FIELD_CREATED_AT = 'created_at';

    protected function _construct()
    {
        $this->_init(ResourceModel\Response\NotFound\Order::class);
    }
}
