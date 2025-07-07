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

namespace HiPay\FullserviceMagento\Model\Method\Providers;

use HiPay\FullserviceMagento\Model\Method\Context;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Applepay config provider
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
abstract class AbstractConfigProvider
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Context         $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $context->getCheckoutSession();
        $this->storeManager = $context->getStoreManager();
        $this->logger = $logger;
    }
    
    /**
     * Resolve a valid store ID for HiPay configuration
     *
     * @return int
     */
    protected function resolveValidStoreId()
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            if ($quote && $quote->getStore()) {
                $storeId = (int) $quote->getStore()->getStoreId();
                if ($storeId > 0) {
                    return $storeId;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning(get_class() . ' : Unable to resolve store ID from quote', [
                'exception' => $e->getMessage()
            ]);
        }

        try {
            $currentStore = $this->storeManager->getStore();
            if ($currentStore) {
                $storeId = (int) $currentStore->getId();
                if ($storeId > 0) {
                    return $storeId;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning(get_class() . ' : Unable to resolve store ID from store manager', [
                'exception' => $e->getMessage()
            ]);
        }
        return 1;
    }
}
