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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Model\Method\Astropay;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;

/**
 * Abstract Model payment method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AbstractAstropay extends AbstractMethodAPI
{
    /**
     *  Use per Astropay' smethods payment
     */
    const IDENTIFICATION_CPF = 'cpf';
    const IDENTIFICATION_CPN = 'cpn';

    protected $_canCapture = false;

    protected $_canCapturePartial = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     *  Extra informations
     *
     * @var array
     */
    protected $_additionalInformationKeys = ['nationalIdentification'];

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

        $nationalIdentificationNumber = $info->getAdditionalInformation('nationalIdentification');
        switch ($this->_typeIdentification) {
            case self::IDENTIFICATION_CPF:
                if (!preg_match(
                    "/(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/",
                    $nationalIdentificationNumber
                )
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('CPF is not correct, please enter a valid CPF.')
                    );
                }
                break;
            case self::IDENTIFICATION_CPN:
                if (!preg_match("/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/", $nationalIdentificationNumber)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('CPN is not correct, please enter a valid CPN.')
                    );
                }
        }
        return $this;
    }

    /*
     *  Get type identification required for payment
     *
     * @return string cpf|cpn
     */
    public function getTypeIdentification()
    {
        return $this->_typeIdentification;
    }
}
