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
    protected const JS_SRC_CONFIG_FINGERPRINT = 'hipay/configurations/fingerprint_js_url';
    protected const JS_SRC_CONFIG_HOSTED_FIELDS = 'hipay/configurations/sdk_js_url';
    protected const JS_SRC_CONFIG_HOSTED_FIELDS_INTEGRITY = 'hipay/configurations/sdk_js_integrity_url';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Psr\Log\LoggerInterface                         $logger
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

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

    /**
     * Return hosted fields integrity URL
     *
     * @return string
     */
    public function getJsSrcHostedFieldsIntegrityUrl()
    {
        return $this->_scopeConfig->getValue(
            self::JS_SRC_CONFIG_HOSTED_FIELDS_INTEGRITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Fetch integrity hash from HiPay server
     *
     * @return string|null
     */
    public function getIntegrityHash()
    {
        $integrityUrl = $this->getJsSrcHostedFieldsIntegrityUrl();
        
        if (empty($integrityUrl)) {
            return null;
        }

        try {
            $httpClient = new \Magento\Framework\HTTP\Client\Curl();
            $httpClient->get($integrityUrl);
            
            if ($httpClient->getStatus() === 200) {
                return trim($httpClient->getBody());
            }
        } catch (\Exception $e) {
            // Log error but don't break the page
            $this->logger->error('Failed to fetch HiPay SDK integrity hash: ' . $e->getMessage());
        }
        
        return null;
    }
}
