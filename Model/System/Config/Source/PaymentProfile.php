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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for payment profile
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PaymentProfile implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * PaymentProfile constructor.
     *
     * @param \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $collectionFactory
     */
    public function __construct(
        \HiPay\FullserviceMagento\Model\ResourceModel\PaymentProfile\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->collectionFactory->create()->toOptionArray();
        }
        return $this->options;
    }
}
