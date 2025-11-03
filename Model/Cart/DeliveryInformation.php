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

namespace HiPay\FullserviceMagento\Model\Cart;

/**
 * Delivery Information model
 *
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
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
     * Get the delivery method
     *
     * @return string
     */
    public function getDeliveryMethod()
    {
        return $this->_delivery_method;
    }

    /**
     * Set the delivery method
     *
     * @param  string $delivery_method
     * @return DeliveryInformation
     */
    public function setDeliveryMethod($delivery_method)
    {
        $this->_delivery_method = $delivery_method;
        return $this;
    }

    /**
     * Get the delivery company
     *
     * @return string
     */
    public function getDeliveryCompany()
    {
        return $this->_delivery_company;
    }

    /**
     * Set the delivery company
     *
     * @param  string $delivery_company
     * @return DeliveryInformation
     */
    public function setDeliveryCompany($delivery_company)
    {
        $this->_delivery_company = $delivery_company;
        return $this;
    }

    /**
     * Get the delivery delay
     *
     * @return string
     */
    public function getDeliveryDelay()
    {
        return $this->_delivery_delay;
    }

    /**
     * Set the delivery delay
     *
     * @param  string $delivery_delay
     * @return DeliveryInformation
     */
    public function setDeliveryDelay($delivery_delay)
    {
        $this->_delivery_delay = $delivery_delay;
        return $this;
    }

    /**
     * Get the delivery number
     *
     * @return string
     */
    public function getDeliveryNumber()
    {
        return $this->_delivery_number;
    }

    /**
     * Set the delivery  number
     *
     * @param  string $delivery_number
     * @return DeliveryInformation
     */
    public function setDeliveryNumber($delivery_number)
    {
        $this->_delivery_number = $delivery_number;
        return $this;
    }
}
