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

namespace HiPay\FullserviceMagento\Model\Email\Container;

use Magento\Sales\Model\Order\Email\Container\Container;

/**
 * HiPay Async Confirm Payment Identity Container
 *
 * @author    Kassim Belghait
 * @copyright Copyright (c)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */
class AsyncConfirmPaymentIdentity extends Container
{
    /**
     * Configuration paths
     */
    protected const XML_PATH_EMAIL_COPY_METHOD = 'hipay/async_confirm_payment_email/copy_method';
    protected const XML_PATH_EMAIL_COPY_TO = 'hipay/async_confirm_payment_email/copy_to';
    protected const XML_PATH_EMAIL_IDENTITY = 'hipay/async_confirm_payment_email/identity';
    protected const XML_PATH_EMAIL_TEMPLATE = 'hipay/async_confirm_payment_email/template';
    protected const XML_PATH_EMAIL_ENABLED = 'hipay/async_confirm_payment_email/enabled';

    /**
     * Check if async confirm payment email is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Return copy method (bcc or copy)
     *
     * @return string|null
     */
    public function getCopyMethod(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * Return guest template id
     *
     * @return string|null
     */
    public function getGuestTemplateId(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return string|null
     */
    public function getTemplateId(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return email identity (usually "sales" or "general")
     *
     * @return string|null
     */
    public function getEmailIdentity(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
