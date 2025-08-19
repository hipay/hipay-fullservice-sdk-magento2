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

use HiPay\FullserviceMagento\Api\ResponseNotFoundOrderRepositoryInterface;
use HiPay\FullserviceMagento\Model\ResourceModel\Response\NotFound\Order as ResponseNotFoundOrderResource;
use HiPay\FullserviceMagento\Model\Response\NotFound\Order as ResponseNotFoundOrderModel;
use HiPay\FullserviceMagento\Model\Response\NotFound\OrderFactory as ResponseNotFoundOrderFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * ResponseNotFoundOrderRepository class
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright Copyright (c) 2018 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class ResponseNotFoundOrderRepository implements ResponseNotFoundOrderRepositoryInterface
{
    /**
     * @var ResponseNotFoundOrderFactory
     */
    private $responseNotFoundOrderFactory;

    /**
     * @var ResponseNotFoundOrderResource
     */
    private $responseNotFoundOrderResource;

    /**
     * @var DateTime
     */
    private $dateTime;


    /**
     * @param ResponseNotFoundOrderFactory $responseNotFoundOrderFactory
     * @param ResponseNotFoundOrderResource $responseNotFoundOrderResource
     * @param DateTime $dateTime
     */
    public function __construct(
        ResponseNotFoundOrderFactory  $responseNotFoundOrderFactory,
        ResponseNotFoundOrderResource $responseNotFoundOrderResource,
        DateTime                      $dateTime
    )
    {
        $this->responseNotFoundOrderFactory = $responseNotFoundOrderFactory;
        $this->responseNotFoundOrderResource = $responseNotFoundOrderResource;
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $orderId
     * @return void
     * @throws AlreadyExistsException
     */
    public function savePendingOrder(string $orderId): void
    {
        $model = $this->responseNotFoundOrderFactory->create();
        $model->setData([
            ResponseNotFoundOrderModel::FIELD_ORDER_ID => $orderId,
            'created_at' => $this->dateTime->gmtDate()
        ]);
        $this->responseNotFoundOrderResource->save($model);
    }

    /**
     * @param string $orderId
     * @return void
     * @throws \Exception
     */
    public function deletePendingOrder(string $orderId): void
    {
        $model = $this->responseNotFoundOrderFactory->create();
        $this->responseNotFoundOrderResource->load($model, $orderId, ResponseNotFoundOrderModel::FIELD_ORDER_ID);
        if ($model->getId()) {
            $this->responseNotFoundOrderResource->delete($model);
        }
    }

    /**
     * @param string $orderId
     * @return bool
     */
    public function isPendingOrderExist(string $orderId): bool
    {
        $model = $this->responseNotFoundOrderFactory->create();
        $this->responseNotFoundOrderResource->load($model, $orderId, ResponseNotFoundOrderModel::FIELD_ORDER_ID);
        return (bool)$model->getId();
    }
}
