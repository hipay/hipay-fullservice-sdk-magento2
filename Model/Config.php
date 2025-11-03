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

namespace HiPay\FullserviceMagento\Model;

use HiPay\FullserviceMagento\Model\Config\AbstractConfig;
use HiPay\FullserviceMagento\Model\Method\ApplePay;
use HiPay\FullserviceMagento\Model\System\Config\Source\Environments;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions;
use HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct;
use HiPay\Fullservice\HTTP\Configuration\Configuration as ConfigSDK;
use HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Main Config Class
 * Retrieve general configuration and sources
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Config extends AbstractConfig implements ConfigurationInterface
{
    public const STATUS_AUTHORIZED = 'hipay_authorized';
    public const STATUS_AUTHORIZATION_REQUESTED = 'hipay_authorization_requested';
    public const STATUS_AUTHORIZED_PENDING = 'hipay_authorized_pending';
    public const STATUS_CAPTURE_REQUESTED = 'hipay_capture_requested';
    public const STATUS_CAPTURE_REFUSED = 'hipay_capture_refused';
    public const STATUS_PARTIALLY_CAPTURED = 'hipay_partially_captured';
    public const STATUS_REFUND_REQUESTED = 'hipay_refund_requested';
    public const STATUS_REFUNDED = 'hipay_refunded';
    public const STATUS_REFUND_REFUSED = 'hipay_refund_refused';
    public const STATUS_PARTIALLY_REFUNDED = 'hipay_partially_refunded';
    public const STATUS_EXPIRED = 'hipay_expired';
    public const STATUS_AUTHENTICATION_REQUESTED = 'hipay_authentication_requested';

    protected const CONFIG_HIPAY_KEY_CC_TYPE = 'cctypes_mapper';

    /**
     *
     * @var ConfigurationInterface $_configSDK
     */
    protected $_configSDK;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     *
     * @var \Magento\Framework\App\State $appState
     */
    protected $appState;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * Order Needed for some configurations (Eg. MO/TO credentials ...)
     *
     * @var \Magento\Sales\Model\Order $_order
     */
    protected $_order;

    /**
     * @var bool
     */
    protected $_forceMoto;

    /**
     * @var bool
     */
    protected $_forceStage;

    /**
     * @var bool
     */
    protected $_isApplePay;

    /**
     * @var bool
     */
    protected $_apiEnvStage;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param WriterInterface       $configWriter
     * @param StoreManagerInterface $storeManager
     * @param State                 $appState
     * @param LoggerInterface       $logger
     * @param TypeListInterface     $cacheTypeList
     * @param Pool                  $cacheFrontendPool
     * @param array                 $params
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Store\Model\StoreManagerInterface            $storeManager,
        \Magento\Framework\App\State                          $appState,
        \Psr\Log\LoggerInterface                              $logger,
        \Magento\Framework\App\Cache\TypeListInterface        $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool            $cacheFrontendPool,
        array $params = []
    ) {
        parent::__construct($scopeConfig, $configWriter);

        $this->_storeManager = $storeManager;
        $this->appState = $appState;
        $this->logger = $logger;
        if ($params) {
            if (isset($params['methodCode'])) {
                $method = $params['methodCode'];
                $this->setMethod($method);
            }
            if (isset($params['storeId'])) {
                $storeId = $params['storeId'];
                $this->setStoreId($storeId);
            }

            if (isset($params['order']) && $params['order'] instanceof \Magento\Sales\Model\Order) {
                $this->setOrder($params['order']);
            }

            if (isset($params['apiEnv'])) {
                $this->_apiEnvStage = $this->getGeneraleValue('api_environment', 'hipay_api_environment')
                    == ConfigSDK::API_ENV_STAGE;
            }
        }

        $this->_forceMoto = isset($params['forceMoto']) ? $params['forceMoto'] : false;
        $this->_forceStage = isset($params['forceStage']) ? $params['forceStage'] : false;
        $this->_isApplePay = isset($params['isApplePay']) ? $params['isApplePay'] :
            $this->getMethodCode() === ApplePay::HIPAY_METHOD_CODE;

        $apiUsername = $this->getApiUsername();
        $apiPassword = $this->getApiPassword();
        try {
            $env = $this->_apiEnvStage === true ? ConfigSDK::API_ENV_STAGE : $this->getApiEnv();
            if ($env == null) {
                $env = ($this->_forceStage) ? ConfigSDK::API_ENV_STAGE : ConfigSDK::API_ENV_PRODUCTION;
            }
            $this->_configSDK = new ConfigSDK(
                [
                    'apiUsername' => $apiUsername,
                    'apiPassword' => $apiPassword,
                    'apiEnv' => $env,
                    'apiHTTPHeaderAccept' => 'application/json',
                    'proxy' => $this->getProxy(),
                    'hostedPageV2' => true
                ]
            );
        } catch (\Exception $e) {
            $this->_configSDK = null;
        }
    }

    /**
     * Check if we must to use MO/TO credentials
     *
     * Essentially, Admin operations
     *
     * @return bool
     */
    public function mustUseMotoCredentials()
    {
        $hasOrder = $this->getOrder() !== null;
        $hasLastTransId = false;
        $isMoto = false;

        if ($this->_forceMoto) {
            return true;
        }

        if ($hasOrder) {
            $hasLastTransId = $this->getOrder()->getPayment()->getCcTransId() ? true : false;
            $isMoto = $this->isMoto();
        }

        return $this->isAdminArea() && $hasOrder && (!$hasLastTransId || ($hasLastTransId && $isMoto));
    }

    /**
     * Return if current store is admin
     *
     * @return bool
     */
    public function isAdminArea()
    {
        return $this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    /**
     * Check if the current order is a MOTO
     *
     * @return bool
     */
    public function isMoto()
    {
        return (bool) $this->getOrder()->getPayment()->getAdditionalInformation('is_moto') ?: false;
    }

    /**
     * Retrieve the list of payment products
     *
     * @return false|string[]
     */
    public function getPaymentProductsList()
    {
        $list = explode(',', $this->getValue('payment_products') ?: '');
        return $list;
    }

    /**
     * Retrieve allowed HiPay payment product categories
     *
     * @return false|string[]
     */
    public function getPaymentProductCategoryList()
    {
        return $this->getAllowedPaymentProductCategories();
    }

    /**
     * Convert payment products to option array format
     *
     * @return array
     */
    public function getPaymentProductsToOptionArray()
    {
        $list = [];
        foreach ($this->getPaymentProducts() as $paymentProduct) {
            $list[] = ['value' => $paymentProduct->getProductCode(), 'label' => $paymentProduct->getBrandName()];
        }
        return $list;
    }

    /**
     * Payment products source getter
     *
     * @return array
     */
    public function getPaymentProducts()
    {
        $pp = (new PaymentProduct())->getPaymentProducts($this->getAllowedPaymentProductCategories());
        return $pp;
    }

    /**
     * Retrieve configured allowed product categories
     *
     * @return false|string[]
     */
    public function getAllowedPaymentProductCategories()
    {
        return explode(',', $this->getValue('payment_products_categories') ?: '');
    }

    /**
     * Payment actions source getter
     *
     * @return array
     */
    public function getPaymentActions()
    {
        return (new PaymentActions())->getPaymentActions();
    }

    /**
     * Environments source getter
     *
     * @return array
     */
    public function getEnvironments()
    {
        return (new Environments())->getEnvironments();
    }

    /**
     * Check if current configuration uses the staging API environment
     *
     * @return bool
     */
    public function isStageMode()
    {
        return $this->getApiEnv() == ConfigSDK::API_ENV_STAGE || $this->_forceStage || $this->_apiEnvStage;
    }

    /**
     * Check if current configuration uses the staging API environment
     *
     * @param bool $withTokenJs
     * @param bool $withApplePay
     * @return bool
     */
    public function hasCredentials(bool $withTokenJs = false, bool $withApplePay = false)
    {
        if ($withApplePay) {
            //token JS credential
            $apiUsernameApplepay = $this->getApiUsernameApplePay();
            $apiPasswordApplepay = $this->getApiPasswordApplePay();
            $secretKeyApplePay = $this->getSecretPassphraseApplePay();

            if (empty($apiUsernameApplepay) || empty($apiPasswordApplepay || empty($secretKeyApplePay))) {
                return false;
            }
        }

        if ($withTokenJs) {
            //token JS credential
            $apiUsernameTokenJs = $this->getApiUsernameTokenJs();
            $apiPasswordTokenJs = $this->getApiPasswordTokenJs();

            if (empty($apiUsernameTokenJs) || empty($apiPasswordTokenJs)) {
                return false;
            }
        }

        //default api username, password, secret passphrase
        $apiUsername = $this->getApiUsername();
        $apiPassword = $this->getApiPassword();
        $secretKey = $this->getSecretPassphrase();

        //return false if one of them if empty
        if (empty($apiUsername) || empty($apiPassword) || empty($secretKey)) {
            return false;
        }

        return true;
    }

    /**
     * Get API username
     *
     * @return mixed|string
     */
    public function getApiUsername()
    {
        if ($this->_isApplePay) {
            return $this->getApiUsernameApplePay();
        }

        if ($this->mustUseMotoCredentials()) {
            return $this->getApiUsernameMoto();
        }

        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }

        return $this->getGeneraleValue($key);
    }

    /**
     * Get Api Password
     *
     * @return mixed|string
     */
    public function getApiPassword()
    {
        if ($this->_isApplePay) {
            return $this->getApiPasswordApplePay();
        }

        if ($this->mustUseMotoCredentials()) {
            return $this->getApiPasswordMoto();
        }

        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key);
    }

    /**
     * Get Secret Passphrase
     *
     * @return mixed
     */
    public function getSecretPassphrase()
    {
        if ($this->_isApplePay) {
            return $this->getSecretPassphraseApplePay();
        }

        if ($this->mustUseMotoCredentials()) {
            return $this->getSecretPassphraseMoto();
        }
        $key = 'secret_passphrase';
        if ($this->isStageMode()) {
            $key = 'secret_passphrase_test';
        }

        return $this->getGeneraleValue($key);
    }

    /**
     * Get Api Username for Moto
     *
     * @return mixed
     */
    public function getApiUsernameMoto()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }

        $apiUsername = $this->getGeneraleValue($key, 'hipay_credentials_moto');
        if (empty($apiUsername)) {
            $apiUsername = $this->getGeneraleValue($key);
        }

        return $apiUsername;
    }

    /**
     * Get api password for Moto
     *
     * @return mixed
     */
    public function getApiPasswordMoto()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        $apiPassword = $this->getGeneraleValue($key, 'hipay_credentials_moto');
        if (empty($apiPassword)) {
            $apiPassword = $this->getGeneraleValue($key);
        }

        return $apiPassword;
    }

    /**
     * Get Secret Passphrase for Moto
     *
     * @return mixed
     */
    public function getSecretPassphraseMoto()
    {
        $key = 'secret_passphrase';
        if ($this->isStageMode()) {
            $key = 'secret_passphrase_test';
        }

        $apiPassphrase = $this->getGeneraleValue($key, 'hipay_credentials_moto');
        if (empty($apiPassphrase)) {
            $apiPassphrase = $this->getGeneraleValue($key);
        }

        return $apiPassphrase;
    }

    /**
     * Get API username for TokenJS credentials
     *
     * @return mixed
     */
    public function getApiUsernameTokenJs()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_tokenjs');
    }

    /**
     * Get API password for TokenJS credentials
     *
     * @return mixed
     */
    public function getApiPasswordTokenJs()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_tokenjs');
    }

    /**
     * Get API username for Apple Pay credentials
     *
     * @return mixed
     */
    public function getApiUsernameApplePay()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    /**
     *  Get API password for Apple Pay credentials
     *
     * @return mixed
     */
    public function getApiPasswordApplePay()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    /**
     * Get secret passphrase for Apple Pay credentials
     *
     * @return mixed
     */
    public function getSecretPassphraseApplePay()
    {
        $key = 'secret_passphrase';
        if ($this->isStageMode()) {
            $key = 'secret_passphrase_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    /**
     * Get API username for Apple Pay TokenJS credentials
     *
     * @return mixed
     */
    public function getApiUsernameApplePayTokenJs()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_applepay_tokenjs');
    }

    /**
     * Get API password for Apple Pay TokenJS credentials
     *
     * @return mixed
     */
    public function getApiPasswordApplePayTokenJs()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay_tokenjs');
    }

    /**
     * Get hashing algorithm configuration for credentials
     *
     * @return mixed
     */
    public function getHashingAlgorithm()
    {
        $group = 'hipay_credentials';
        if ($this->_isApplePay) {
            $group = 'hipay_credentials_applepay';
        } elseif ($this->mustUseMotoCredentials()) {
            $group = 'hipay_credentials_moto';
        }

        $key = 'hashing_algorithm';
        if ($this->isStageMode()) {
            $key = 'hashing_algorithm_test';
        }
        return $this->getGeneraleValue($key, $group);
    }

    /**
     * Set hashing algorithm configuration for credentials
     *
     * @param mixed  $hash
     * @param string $scope
     * @return void
     */
    public function setHashingAlgorithm($hash, string $scope = ScopeInterface::SCOPE_STORES)
    {
        $group = 'hipay_credentials';
        if ($this->_isApplePay) {
            $group = 'hipay_credentials_applepay';
        } elseif ($this->mustUseMotoCredentials()) {
            $group = 'hipay_credentials_moto';
        }

        $key = 'hashing_algorithm';
        if ($this->isStageMode()) {
            $key = 'hashing_algorithm_test';
        }
        $this->setGeneralValue($key, $hash, $group, $scope);
    }

    /**
     * Get proxy configuration for HiPay API calls
     *
     * @return array
     */
    public function getProxy()
    {
        $group = 'hipay_proxy_settings';

        if (empty($this->getGeneraleValue('hipay_proxy_host', $group))) {
            return [];
        }

        return [
            'host' => $this->getGeneraleValue('hipay_proxy_host', $group),
            'port' => $this->getGeneraleValue('hipay_proxy_port', $group),
            'user' => $this->getGeneraleValue('hipay_proxy_user', $group),
            'password' => $this->getGeneraleValue('hipay_proxy_password', $group),
        ];
    }

    /**
     *  Get other configuration
     *
     * @param string $key
     * @return boolean
     */
    private function getOtherConfiguration(string $key)
    {
        return $this->getGeneraleValue($key, 'configurations');
    }

    /**
     *  Get Send Notification Url
     *
     * @return boolean
     */
    public function isSendingNotifyUrl()
    {
        $key = 'send_notification_url';
        return $this->getOtherConfiguration($key);
    }

    /**
     *  Get Fingerprint configuration
     *
     * @return boolean
     */
    public function isFingerprintEnabled()
    {
        $key = 'fingerprint_enabled';
        return $this->getOtherConfiguration($key);
    }

    /**
     *  Get Basket Enabled configuration
     *
     * @return boolean
     */
    public function isBasketEnabled()
    {
        $key = 'basket_enabled';
        return $this->getOtherConfiguration($key);
    }

    /**
     * Determine whether to use the order currency for the transaction.
     *
     * @return bool
     */
    public function useOrderCurrency()
    {
        $key = 'currency_transaction';
        return $this->getOtherConfiguration($key);
    }

    /**
     * Check if sending Cart items is necessary
     *
     * @param string $product_code
     * @return bool
     */
    public function isNecessaryToSendCartItems($product_code)
    {
        if (($this->isBasketEnabled() || $this->isBasketRequired($product_code)) && !$this->isBasketForcedDisabled()) {
            return true;
        }
        return false;
    }

    /**
     * Basket is forced disabled for some payment method
     *
     * @return bool
     */
    public function isBasketForcedDisabled()
    {
        return false;
    }

    /**
     *  Check if basket is required for the payment product
     *
     * @param string $product_code
     * @return boolean True if basket is required/ False if method doesn't exist
     */
    private function isBasketRequired($product_code)
    {
        $payment_product = \HiPay\Fullservice\Data\PaymentProduct\Collection::getItem($product_code);
        return $payment_product ? $payment_product->getBasketRequired() : false;
    }

    /**
     *  Get Custom Ean attribute configuration
     *
     * @return boolean
     */
    public function getEanAttribute()
    {
        $key = 'basket_attribute_ean';
        return $this->getOtherConfiguration($key);
    }

    /**
     *  Delivery information are mandatory for some payment product
     *
     * @param  string $product_code
     * @return boolean
     */
    public function isDeliveryMethodRequired($product_code)
    {
        return in_array($product_code, ['3xcb', '3xcb-no-fees', '4xcb-no-fees', '4xcb', 'credit-long']);
    }

    /**
     *  Get current github version info
     *
     * @return \stdClass
     */
    public function getVersionInfo()
    {
        $key = 'github_module_version';
        $this->setStoreId(0);

        return $this->getGeneraleValue($key, 'hipay_module');
    }

    /**
     * Set Module version Information
     *
     * @param false|string $info
     */
    public function setModuleVersionInfo($info)
    {
        $key = 'github_module_version';
        $this->setStoreId(0);
        $this->setGeneralValue($key, $info, 'hipay_module', 'default');
        $this->_storeManager->getStore(0)->resetConfig();
    }

    /**
     * Get Api Endpoint
     *
     * @return mixed|string
     */
    public function getApiEndpoint()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpoint() : '';
    }

    /**
     * Get Api Endpoint V2
     *
     * @return mixed|string
     */
    public function getApiEndpointV2()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2() : '';
    }

    /**
     * Get Api Endpoint prod
     *
     * @return string
     */
    public function getApiEndpointProd()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointProd() : '';
    }

    /**
     * Get Api Endpoint V2 prod
     *
     * @return string
     */
    public function getApiEndpointV2Prod()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2Prod() : '';
    }

    /**
     * Get Api Endpoint stage
     *
     * @return string
     */
    public function getApiEndpointStage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointStage() : '';
    }

    /**
     * Get Api Endpoint V2 stage
     *
     * @return string
     */
    public function getApiEndpointV2Stage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2Stage() : '';
    }

    /**
     *  Get Secure Vault production endpoint
     *
     * @return string
     */
    public function getSecureVaultEndpointProd()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpointProd() : '';
    }

    /**
     * Get Secure Vault stage endpoint
     *
     * @return string
     */
    public function getSecureVaultEndpointStage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpointStage() : '';
    }

    /**
     * Get Secure Vault endpoint
     *
     * @return string
     */
    public function getSecureVaultEndpoint()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpoint() : '';
    }

    /**
     * Get configured CURL timeout for API requests
     *
     * @return int|string
     */
    public function getCurlTimeout()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getCurlTimeout() : '';
    }

    /**
     * Set CURL timeout value for API requests
     *
     * @param int|float $curlTimeout
     * @return void
     */
    public function setCurlTimeout($curlTimeout)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setCurlTimeout($curlTimeout);
        }
    }

    /**
     * Get configured CURL connection timeout
     *
     * @return int|float
     */
    public function getCurlConnectTimeout()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getCurlConnectTimeout() : '';
    }

    /**
     * Set CURL connection timeout value
     *
     * @param int|float $curlConnectTimeout
     * @return void
     */
    public function setCurlConnectTimeout($curlConnectTimeout)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setCurlConnectTimeout($curlConnectTimeout);
        }
    }

    /**
     * Get API environment
     *
     * @return string|null
     */
    public function getApiEnv()
    {
        return $this->getValue('env');
    }

    /**
     * Get SDK JavaScript URL
     *
     * @return bool
     */
    public function getSdkJsUrl()
    {
        return $this->getOtherConfiguration('sdk_js_url');
    }

    /**
     * Get API HTTP header Accept value
     *
     * @return mixed|string
     */
    public function getApiHTTPHeaderAccept()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiHTTPHeaderAccept() : '';
    }

    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Set Order
     *
     * @param Order $order
     * @return void
     */
    public function setOrder($order)
    {
        $this->_order = $order;
    }

    /**
     * Check if payment method is active in configuration
     *
     * @return string|null
     */
    public function isPaymentMethodActive()
    {
        return $this->getValue('active');
    }

    /**
     * Retrieve mapper between Magento and HiPay
     *
     * @return array
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::CONFIG_HIPAY_KEY_CC_TYPE),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::setApiPassword()
     */
    public function setApiPassword($apiPassword)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setApiPassword($apiPassword);
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::setApiUsername()
     */
    public function setApiUsername($apiUsername)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setApiUsername($apiUsername);
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::setApiEnv()
     */
    public function setApiEnv($apiEnv)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setApiEnv($apiEnv);
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::setProxy()
     */
    public function setProxy($proxy)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setProxy($proxy);
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::setApiHTTPHeaderAccept()
     */
    public function setApiHTTPHeaderAccept($apiHTTPHeaderAccept)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setApiHTTPHeaderAccept($apiHTTPHeaderAccept);
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::getDataApiEndpointProd()
     */
    public function getDataApiEndpointProd()
    {
        if ($this->_configSDK !== null) {
            return $this->_configSDK->getDataApiEndpointProd();
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::getDataApiEndpointStage()
     */
    public function getDataApiEndpointStage()
    {
        if ($this->_configSDK !== null) {
            return $this->_configSDK->getDataApiEndpointStage();
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::getDataApiEndpoint()
     */
    public function getDataApiEndpoint()
    {
        if ($this->_configSDK !== null) {
            return $this->_configSDK->getDataApiEndpoint();
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::getDataApiHttpUserAgent()
     */
    public function getDataApiHttpUserAgent()
    {
        if ($this->_configSDK !== null) {
            return $this->_configSDK->getDataApiHttpUserAgent();
        }
    }

    /**
     * @inheritDoc
     *
     * @see \HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface::isOverridePaymentProductSorting()
     */
    public function isOverridePaymentProductSorting()
    {
        return false;
    }

    /**
     * Sets override sorting payment products parameter
     *
     * @param bool $overridePaymentProductSorting
     */
    public function setOverridePaymentProductSorting($overridePaymentProductSorting)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setOverridePaymentProductSorting($overridePaymentProductSorting);
        }
    }

    /**
     * Returns hostedpage v2 parameter
     *
     * @return bool
     */
    public function isHostedPageV2()
    {
        if ($this->_configSDK !== null) {
            return $this->_configSDK->isHostedPageV2();
        }
    }

    /**
     * Sets hostedpage v2 parameter
     *
     * @param bool $hostedPageV2
     */
    public function setHostedPageV2($hostedPageV2)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setHostedPageV2($hostedPageV2);
        }
    }

    /**
     * Check if notification cron job is enabled
     *
     * @return mixed
     */
    public function isNotificationCronActive()
    {
        $key = 'notifications_cron';
        return $this->getGeneraleValue($key, 'hipay_notifications');
    }
}
