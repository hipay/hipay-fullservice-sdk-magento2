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

namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Card data model
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Card _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Card getResource()
 * @method int getCustomerId()
 * @method \HiPay\FullserviceMagento\Model\Card setCustomerId(int $customerId)
 * @method string getName()
 * @method \HiPay\FullserviceMagento\Model\Card setName(string $name)
 * @method string getCcExpMonth()
 * @method \HiPay\FullserviceMagento\Model\Card setCcExpMonth(string $ccExpMonth)
 * @method string getCcExpYear()
 * @method \HiPay\FullserviceMagento\Model\Card setCcExpYear(string $ccExpYear)
 * @method string getCcSecureVerify()
 * @method \HiPay\FullserviceMagento\Model\Card setCcSecureVerify(string $ccSecureVerify)
 * @method string getCclast4()
 * @method \HiPay\FullserviceMagento\Model\Card setCclast4(string $cclast4)
 * @method string getCcOwner()
 * @method \HiPay\FullserviceMagento\Model\Card setCcOwner(string $ccOwner)
 * @method string getCcType()
 * @method \HiPay\FullserviceMagento\Model\Card setCcType(string $ccType)
 * @method string getCcNumberEnc()
 * @method \HiPay\FullserviceMagento\Model\Card setCcNumberEnc(string $ccNumberEnc)
 * @method int getCcStatus()
 * @method \HiPay\FullserviceMagento\Model\Card setCcStatus(int $ccStatus)
 * @method string getCcToken()
 * @method \HiPay\FullserviceMagento\Model\Card setCcToken(int $ccToken)
 * @method int getIsDefault()
 * @method \HiPay\FullserviceMagento\Model\Card setIsDefault(int $isDefault)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Card extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\Card');
        $this->setIdFieldName('card_id');
    }
}
