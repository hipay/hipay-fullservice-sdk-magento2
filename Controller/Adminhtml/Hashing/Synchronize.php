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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\Hashing;

use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayFactory;
use Psr\Log\LoggerInterface;

/**
 * Add new condition html on rule edition
 * Used for 3ds and oneclick in payment configuration
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
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
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @var \HiPay\FullserviceMagento\Helper\Data
     */
    protected $_hipayHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigFactory $configFactory,
        GatewayFactory $gatewayFactory,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
        $this->_gatewayFactory = $gatewayFactory;
        $this->_hipayHelper = $hipayHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $storeId = $this->_getConfigScopeStoreId();
        $this->_updateHashAlgorithm($storeId);
        $this->setRedirect();
    }

    /**
     * @param $storeId
     */
    protected function _updateHashAlgorithm($storeId)
    {

        $allParams = array(
            array(
                'storeId' => $storeId,
                'forceMoto' => false,
                'forceStage' => false
            ),
            array(
                'storeId' => $storeId,
                'forceMoto' => false,
                'forceStage' => true
            ),
            array(
                'storeId' => $storeId,
                'forceMoto' => true,
                'forceStage' => false
            ),
            array(
                'storeId' => $storeId,
                'forceMoto' => true,
                'forceStage' => true
            )
        );

        $store = $this->_storeManager->getStore($storeId);

        foreach ($allParams as $params) {
            /** @var $config \HiPay\FullserviceMagento\Model\Config */
            $config = $this->_configFactory->create(array('params' => $params));
            if ($config->hasCredentials()) {
                $gatewayClient = $this->_gatewayFactory->create(null, $params);
                $scope = ('' !== $this->store) ? \Magento\Store\Model\ScopeInterface::SCOPE_STORES : 'default';
                try {
                    $this->_hipayHelper->updateHashAlgorithm($config, $gatewayClient, $store, $scope);
                } catch (\HiPay\Fullservice\Exception\RuntimeException $e) {
                    $this->messageManager->addErrorMessage(__("We can't synchronize at least one of the account"));
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     *
     *
     * @return int
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
     *  Redirect after action ( With current store configuration )
     */
    private function setRedirect()
    {
        $this->_redirect(
            'adminhtml/system_config/edit',
            array(
                '_secure' => true,
                'section' => 'hipay',
                'store' => $this->store,
                'website' => $this->website
            )
        );
    }

}
