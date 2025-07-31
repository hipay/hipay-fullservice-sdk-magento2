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

/**
 * Main Config Class
 * Retrieve general configuration and sources
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
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

    protected $_apiEnvStage;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\App\State                          $appState
     * @param \Psr\Log\LoggerInterface                              $logger
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface        $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool            $cacheFrontendPool
     * @param array                                                 $params
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        $params = []
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
     * @return bool
     */
    public function isMoto()
    {
        return (bool) $this->getOrder()->getPayment()->getAdditionalInformation('is_moto') ?: false;
    }

    /**
     *
     */
    public function getPaymentProductsList()
    {
        $list = explode(',', $this->getValue('payment_products') ?: '');
        return $list;
    }

    public function getPaymentProductCategoryList()
    {
        return $this->getAllowedPaymentProductCategories();
    }

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

    public function isStageMode()
    {
        return $this->getApiEnv() == ConfigSDK::API_ENV_STAGE || $this->_forceStage || $this->_apiEnvStage;
    }

    public function hasCredentials($withTokenJs = false, $withApplePay = false)
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

    public function getApiUsernameTokenJs()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_tokenjs');
    }

    public function getApiPasswordTokenJs()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_tokenjs');
    }

    public function getApiUsernameApplePay()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    public function getApiPasswordApplePay()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    public function getSecretPassphraseApplePay()
    {
        $key = 'secret_passphrase';
        if ($this->isStageMode()) {
            $key = 'secret_passphrase_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay');
    }

    public function getApiUsernameApplePayTokenJs()
    {
        $key = 'api_username';
        if ($this->isStageMode()) {
            $key = 'api_username_test';
        }
        return $this->getGeneraleValue($key, 'hipay_credentials_applepay_tokenjs');
    }

    public function getApiPasswordApplePayTokenJs()
    {
        $key = 'api_password';
        if ($this->isStageMode()) {
            $key = 'api_password_test';
        }

        return $this->getGeneraleValue($key, 'hipay_credentials_applepay_tokenjs');
    }

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

    public function setHashingAlgorithm($hash, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES)
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
     * @param  $key
     * @return boolean
     */
    private function getOtherConfiguration($key)
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
     * @param  $product_code
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
     * @param  $product_code
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
     * @param $info module version info from github
     */
    public function setModuleVersionInfo($info)
    {
        $key = 'github_module_version';
        $this->setStoreId(0);
        $this->setGeneralValue($key, $info, 'hipay_module', 'default');
        $this->_storeManager->getStore(0)->resetConfig();
    }

    public function getApiEndpoint()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpoint() : '';
    }

    public function getApiEndpointV2()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2() : '';
    }

    public function getApiEndpointProd()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointProd() : '';
    }

    public function getApiEndpointV2Prod()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2Prod() : '';
    }

    public function getApiEndpointStage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointStage() : '';
    }

    public function getApiEndpointV2Stage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiEndpointV2Stage() : '';
    }

    public function getSecureVaultEndpointProd()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpointProd() : '';
    }

    public function getSecureVaultEndpointStage()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpointStage() : '';
    }

    public function getSecureVaultEndpoint()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getSecureVaultEndpoint() : '';
    }

    public function getCurlTimeout()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getCurlTimeout() : '';
    }

    public function setCurlTimeout($curlTimeout)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setCurlTimeout($curlTimeout);
        }
    }

    public function getCurlConnectTimeout()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getCurlConnectTimeout() : '';
    }

    public function setCurlConnectTimeout($curlConnectTimeout)
    {
        if ($this->_configSDK !== null) {
            $this->_configSDK->setCurlConnectTimeout($curlConnectTimeout);
        }
    }

    public function getApiEnv()
    {
        return $this->getValue('env');
    }

    public function getSdkJsUrl()
    {
        return $this->getOtherConfiguration('sdk_js_url');
    }



    public function getApiHTTPHeaderAccept()
    {
        return $this->_configSDK !== null ? $this->_configSDK->getApiHTTPHeaderAccept() : '';
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function setOrder($order)
    {
        $this->_order = $order;
    }

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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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

    public function isNotificationCronActive()
    {
        $key = 'notifications_cron';
        return $this->getGeneraleValue($key, 'hipay_notifications');
    }
}
