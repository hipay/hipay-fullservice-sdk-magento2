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

namespace HiPay\FullserviceMagento\Model\Method\Astropay;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;
use Magento\Framework\Exception\LocalizedException;

/**
 * Abstract Model payment method
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AbstractAstropay extends AbstractMethodAPI
{
    /**
     *  Use per Astropay' smethods payment
     */
    protected const IDENTIFICATION_CPF = 'cpf';
    protected const IDENTIFICATION_CPN = 'cpn';

    /**
     *  Extra informations
     *
     * @var array
     */
    protected $_additionalInformationKeys = ['nationalIdentification', 'cc_type'];

    /**
     * Assign data to info model instance
     *
     * @param  \Magento\Framework\DataObject $additionalData
     * @return $this
     * @throws LocalizedException
     */
    public function _assignAdditionalInformation(\Magento\Framework\DataObject $additionalData)
    {
        parent::_assignAdditionalInformation($additionalData);
        $info = $this->getInfoInstance();
        $info->setCcType($additionalData->getCcType());

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        /*
        * calling parent validate function
        */
        parent::validate();
        $info = $this->getInfoInstance();

        if (!$info->getCcType()) {
            return $this;
        }

        $nationalIdentificationNumber = $info->getAdditionalInformation('nationalIdentification');
        switch ($this->_typeIdentification) {
            case self::IDENTIFICATION_CPF:
                if (
                    !preg_match(
                        "/(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/",
                        $nationalIdentificationNumber ?: ''
                    )
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('CPF is not correct, please enter a valid CPF.')
                    );
                }
                break;
            case self::IDENTIFICATION_CPN:
                if (!preg_match("/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/", $nationalIdentificationNumber ?: '')) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('CPN is not correct, please enter a valid CPN.')
                    );
                }
        }
        return $this;
    }

    /**
     *  Get type identification required for payment
     *
     * @return string cpf|cpn
     */
    public function getTypeIdentification()
    {
        return $this->_typeIdentification;
    }
}
