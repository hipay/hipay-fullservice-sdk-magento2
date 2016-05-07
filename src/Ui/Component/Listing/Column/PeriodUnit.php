<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;


/**
 * Class PeriodUnit
 */
class PeriodUnit implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory;
     */
    protected $ppFactory;

    /**
     * Constructor
     *
     * @param \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory;
     */
    public function __construct(\HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory)
    {
        $this->ppFactory = $ppFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->ppFactory->create()->getAllPeriodUnits();
        }
        return $this->options;
    }
}
