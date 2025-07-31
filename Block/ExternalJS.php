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
     * Return hosted fields URL and integrity hash
     *
     * @return array
     */
    public function getJsSrcHostedFields()
    {
        $sdkUrl = $this->_scopeConfig->getValue(
            self::JS_SRC_CONFIG_HOSTED_FIELDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
        
        $integrityHash = null;
        if (!empty($sdkUrl)) {
            $integrityHash = $this->getIntegrityHash($sdkUrl);
        }
        
        return [
            'sdkUrl' => $sdkUrl,
            'integrityHash' => $integrityHash
        ];
    }

    /**
     * Fetch integrity hash from HiPay server
     *
     * @param string $sdkUrl The SDK URL to generate integrity URL from
     * @return string|null
     */
    public function getIntegrityHash($sdkUrl = null)
    {
        if ($sdkUrl === null) {
            $sdkUrl = $this->_scopeConfig->getValue(
                self::JS_SRC_CONFIG_HOSTED_FIELDS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
        }
        
        if (empty($sdkUrl)) {
            return null;
        }
        
        // Replace .js with .integrity to generate the integrity URL
        $integrityUrl = str_replace('.js', '.integrity', $sdkUrl);

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
