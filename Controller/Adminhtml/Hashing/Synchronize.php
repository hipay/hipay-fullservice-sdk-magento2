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

namespace HiPay\FullserviceMagento\Controller\Adminhtml\Hashing;

use HiPay\Fullservice\Exception\ApiErrorException;
use HiPay\Fullservice\Exception\RuntimeException;
use HiPay\FullserviceMagento\Helper\Data;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add new condition html on rule edition
 * Used for 3ds and oneclick in payment configuration
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Synchronize extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $store;

    /**
     * @var string
     */
    protected $website;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     *
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     *
     * @var GatewayFactory
     */
    protected $_gatewayFactory;

    /**
     * @var Data
     */
    protected $_hipayHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ConfigFactory $configFactory
     * @param GatewayFactory $gatewayFactory
     * @param Data $hipayHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        ConfigFactory         $configFactory,
        GatewayFactory        $gatewayFactory,
        Data                  $hipayHelper,
        LoggerInterface       $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
        $this->_gatewayFactory = $gatewayFactory;
        $this->_hipayHelper = $hipayHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Synchronize hash algorithm for the current store and redirect to configuration
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $storeId = $this->_getConfigScopeStoreId();
        $this->_updateHashAlgorithm($storeId);
        $this->setRedirect();
    }

    /**
     * Update hash algorithm for all configured platforms using store credentials.
     *
     * @param int $storeId
     * @throws NoSuchEntityException
     */
    protected function _updateHashAlgorithm(int $storeId)
    {
        $platforms = [
            ConfigFactory::PRODUCTION,
            ConfigFactory::STAGE,
            ConfigFactory::PRODUCTION_MOTO,
            ConfigFactory::STAGE_MOTO,
            ConfigFactory::PRODUCTION_APPLEPAY,
            ConfigFactory::STAGE_APPLEPAY
        ];

        $store = $this->_storeManager->getStore($storeId);
        $scope = ('' !== $this->store) ? ScopeInterface::SCOPE_STORES : 'default';

        foreach ($platforms as $platform) {
            /**
             * @var $config Config
             */
            $config = $this->_configFactory->create(
                ['params' => ['storeId' => $storeId, 'platform' => $platform]]
            );
            if ($config->hasCredentials()) {
                $gatewayClient = $this->_gatewayFactory->create(
                    null,
                    ['storeId' => $storeId, 'platform' => $platform]
                );
                try {
                    $this->_hipayHelper->updateHashAlgorithm($config, $gatewayClient, $store, $scope);
                } catch (RuntimeException|ApiErrorException $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            "We can't synchronize at least one of the account ("
                            . $platform . "). Please check your credentials"
                        )
                    );
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * Resolve store ID from request parameters (store or website).
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _getConfigScopeStoreId()
    {
        $storeId = 0;
        $this->store = $this->getRequest()->getParam('store', '');
        $this->website = $this->getRequest()->getParam('website', '');
        if ('' !== $this->store) {
            $storeId = $this->_storeManager->getStore($this->store)->getId();
        } elseif ('' !== $this->website) {
            $storeId = $this->_storeManager->getWebsite($this->website)->getDefaultStore()->getId();
        }
        return $storeId;
    }

    /**
     * Redirect after action ( With current store configuration )
     *
     * @return void
     */
    private function setRedirect()
    {
        $this->_redirect(
            'adminhtml/system_config/edit',
            [
                '_secure' => true,
                'section' => 'hipay',
                'store' => $this->store,
                'website' => $this->website
            ]
        );
    }
}
