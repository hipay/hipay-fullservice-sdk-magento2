<?php
/**
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\ThreeDS;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo as AccountInfoSDK;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Customer as CustomerInfo;

/**
 * Account info
 *
 * @package HiPay\FullserviceMagento
 * @author HiPay <support@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AccountInfoFormatter extends AbstractRequest
{

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     * @return \HiPay\FullserviceMagento\Model\Request\Info\BillingInfo
     */
    protected function mapRequest()
    {
        $accountInfo = new AccountInfoSDK();

        $accountInfo->customer = $this->getCustomerInfo();

        return $accountInfo;
    }

    protected function getCustomerInfo()
    {
        $customerInfo = new CustomerInfo();

        if ($this->_customerId !== null) {
            $this->_customerSession->getCustomer();
        }

        return $customerInfo;
    }

}
