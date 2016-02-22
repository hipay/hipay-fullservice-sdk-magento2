<?php

namespace HiPay\FullserviceMagento\Model\System\Config\Source;


class CcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT'];
    }
}
