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

use Magento\Framework\App\ResourceConnection;

class OrderState
{
    public const TABLE_SALES_ORDER_STATUS_STATE = 'sales_order_status_state';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get order state based on status
     *
     * @param string $status
     * @return string|null
     */
    public function getOrderStateByStatus(string $status)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $this->resource->getTableName(self::TABLE_SALES_ORDER_STATUS_STATE),
                ['state']
            )
            ->where('status = ?', $status);

        return $connection->fetchOne($select) ?? null;
    }
}
