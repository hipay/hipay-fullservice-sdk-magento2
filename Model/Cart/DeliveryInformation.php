<?php

namespace HiPay\FullserviceMagento\Model\Cart;


use HiPay\Fullservice\Enum\Cart\TypeItems;
use HiPay\Fullservice\Enum\Transaction\Operation;

/**
 * Delivery Information model
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class DeliveryInformation
{

    /**
     *
     * @var string
     */
    private $_delivery_method;

    /**
     *
     * @var string
     */
    private $_delivery_company;

    /**
     *
     * @var string
     */
    private $_delivery_delay;

    /**
     *
     * @var string
     */
    private $_delivery_number;

    /**
     * @return string
     */
    public function getDeliveryMethod()
    {
        return $this->_delivery_method;
    }

    /**
     * @param string $delivery_method
     * @return DeliveryInformation
     */
    public function setDeliveryMethod($delivery_method)
    {
        $this->_delivery_method = $delivery_method;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryCompany()
    {
        return $this->_delivery_company;
    }

    /**
     * @param string $delivery_company
     * @return DeliveryInformation
     */
    public function setDeliveryCompany($delivery_company)
    {
        $this->_delivery_company = $delivery_company;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDelay()
    {
        return $this->_delivery_delay;
    }

    /**
     * @param string $delivery_delay
     * @return DeliveryInformation
     */
    public function setDeliveryDelay($delivery_delay)
    {
        $this->_delivery_delay = $delivery_delay;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryNumber()
    {
        return $this->_delivery_number;
    }

    /**
     * @param string $delivery_number
     * @return DeliveryInformation
     */
    public function setDeliveryNumber($delivery_number)
    {
        $this->_delivery_number = $delivery_number;
        return $this;
    }


}
