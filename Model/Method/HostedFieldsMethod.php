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

namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Hosted Fields Payment Method
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedFieldsMethod extends CcMethod
{
    const HIPAY_METHOD_CODE = 'hipay_hosted_fields';

    /**
     * @var string
     */
    protected $_formBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Info';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;


    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        $info = $this->getInfoInstance();

        if (!$info->getCcType()) {
            return $this;
        }

        $errorMsg = false;

        if (!$info->getAdditionalInformation('card_token')) {
            $errorMsg = __('We can\'t place the order.');
        }

        $availableTypes = explode(',', $this->getConfigData('cctypes'));
        $paymentProduct =  $this->_hipayConfig->getCcTypesMapper();

        if (!in_array($paymentProduct[strtolower($info->getCcType())], $availableTypes)) {
            $errorMsg = __('This credit card type is not allowed for this payment method.');
        }

        if ($errorMsg) {
            throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
        }

        return $this;
    }
}
