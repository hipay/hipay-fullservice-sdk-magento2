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

namespace HiPay\FullserviceMagento\Block;

class ExternalJS extends \Magento\Framework\View\Element\Template
{
    const JS_SRC_CONFIG_FINGERPRINT = 'hipay/configurations/fingerprint_js_url';
    const JS_SRC_CONFIG_HOSTED_FIELDS = 'hipay/configurations/sdk_js_url';

    /**
     * Return figerprint URL
     *
     * @return string
     */
    public function getJsSrcFingerprint()
    {
        return $this->_scopeConfig->getValue(
            self::JS_SRC_CONFIG_FINGERPRINT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Return hosted fields URL
     *
     * @return string
     */
    public function getJsSrcHostedFields()
    {
        return $this->_scopeConfig->getValue(
            self::JS_SRC_CONFIG_HOSTED_FIELDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
    }
}
