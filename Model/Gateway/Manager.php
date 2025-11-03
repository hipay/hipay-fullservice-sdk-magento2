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

namespace HiPay\FullserviceMagento\Model\Gateway;

use HiPay\Fullservice\Enum\Transaction\Operation;
use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\Gateway\Model\AvailablePaymentProduct;
use HiPay\Fullservice\Gateway\Model\HostedPaymentPage;
use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\Fullservice\HTTP\ClientProvider;
use HiPay\Fullservice\HTTP\SimpleHTTPClient;
use HiPay\Fullservice\Request\AbstractRequest;
use HiPay\Fullservice\Request\RequestInterface;
use HiPay\Fullservice\Request\RequestSerializer;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;
use HiPay\FullserviceMagento\Model\Request\Type\Factory as RequestFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Gateway Manager Class
 *
 * HiPay Fullservice SDK is used by the manager
 * So, all api call are centralized here
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Manager
{
    /**
     *   Additional Field to save and generate an operation id
     */
    protected const TRANSACTION_INCREMENT = 'increment_id';

    /**
     * @var Order
     */
    protected $_order;

    /**
     *
     * @var \HiPay\Fullservice\Gateway\Client\GatewayClient $_gateway
     */
    protected $_gateway;

    /**
     *
     * @var HiPay\FullserviceMagento\Model\Config $_config
     */
    protected $_config;

    /**
     *
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     *
     * @var RequestFactory
     */
    protected $_requestFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\FullserviceMethod $_methodInstance
     */
    protected $_methodInstance;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $_transactionRepositoryInterface;

    /**
     * @param RequestFactory                 $requestfactory
     * @param ConfigFactory                  $configFactory
     * @param Data                           $paymentHelper
     * @param LoggerInterface                $logger
     * @param FilterBuilder                  $filterBuilder
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param TransactionRepositoryInterface $repository
     * @param array                          $params
     * @throws LocalizedException
     */
    public function __construct(
        RequestFactory $requestfactory,
        ConfigFactory $configFactory,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Psr\Log\LoggerInterface $logger,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransactionRepositoryInterface $repository,
        array $params = []
    ) {
        $this->_logger = $logger;
        $this->_configFactory = $configFactory;
        $this->_requestFactory = $requestfactory;

        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_transactionRepositoryInterface = $repository;

        if (isset($params['order']) && $params['order'] instanceof Order) {
            $this->_order = $params['order'];
            $methodCode = $this->_order->getPayment()->getMethod();
            $this->_methodInstance = $paymentHelper->getMethodInstance($methodCode);
            $storeId = $this->_order->getStoreId();
            $params = [
                'params' => [
                    'methodCode' => $methodCode,
                    'storeId' => $storeId,
                    'order' => $this->_order,
                    'forceMoto' => (isset($params['forceMoto'])) ? $params['forceMoto'] : false
                ]
            ];
        } else {
            $storeId = (isset($params['storeId'])) ? $params['storeId'] : false;
            $platform = (isset($params['platform'])) ? $params['platform'] : false;
            $apiEnv = (isset($params['apiEnv'])) ? $params['apiEnv'] : false;
            $params = [
                'params' => [
                    'storeId' => $storeId,
                    'platform' => $platform,
                    'apiEnv' => $apiEnv
                ]
            ];
        }

        $this->_config = $this->_configFactory->create($params);

        $clientProvider = new SimpleHTTPClient($this->_config);
        $this->_gateway = new GatewayClient($clientProvider);
    }

    /**
     * Get Client Provider
     *
     * @return ClientProvider
     */
    public function getClientProvider()
    {
        return $this->_gateway->getClientProvider();
    }

    /**
     * Get Configuration
     *
     * @return Config
     */
    public function getConfiguration()
    {
        return $this->_config;
    }

    /**
     * Request hosted payment page
     *
     * @return HostedPaymentPage
     * @throws \Exception
     */
    public function requestHostedPaymentPage()
    {

        //Merge params
        $params = $this->_getRequestParameters();
        $params['params']['paymentMethod'] = $this->_getPaymentMethodRequest();

        /**
         * @var $hpp \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
        */
        $hpp = $this->_getRequestObject(\HiPay\FullserviceMagento\Model\Request\HostedPaymentPage::class, $params);
        $this->_debug($this->_requestToArray($hpp));

        /**
         * @var $hppModel HostedPaymentPage
        */
        try {
            $hppModel = $this->_gateway->requestHostedPaymentPage($hpp);
            $this->_debug($hppModel->toArray());
        } catch (\Exception $e) {
            // Just log because Magento Core doesn't log
            $this->_logger->critical($e);
            throw $e;
        }

        if ($this->getConfiguration()->isAdminArea()) {
            $this->_order->getPayment()->setAdditionalInformation('is_moto', 1);
            $this->_order->save();
        }

        return $hppModel;
    }

    /**
     * Request New Order
     *
     * @return Transaction
     * @throws \Exception
     */
    public function requestNewOrder()
    {
        $params = $this->_getRequestParameters();
        $params['params']['operation'] = 'Authorization';
        $params['params']['paymentMethod'] = $this->_getPaymentMethodRequest();

        $orderRequest = $this->_getRequestObject(\HiPay\FullserviceMagento\Model\Request\Order::class, $params);
        $this->_debug($this->_requestToArray($orderRequest));

        //Request new order transaction
        try {
            $transaction = $this->_gateway->requestNewOrder($orderRequest);
        } catch (\Exception $e) {
            // Just log because Magento Core doesn't log
            $this->_logger->critical($e);
            throw $e;
        }

        //If is admin area set mo/to value to payment additionnal informations
        if ($this->getConfiguration()->isAdminArea()) {
            $this->_order->getPayment()->setAdditionalInformation('is_moto', 1);
            $this->_order->save();
        }

        return $transaction;
    }

    /**
     * Request Operation Capture
     *
     * @param float|null $amount
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    public function requestOperationCapture(float $amount = null)
    {
        return $this->_requestOperation(Operation::CAPTURE, $amount);
    }

    /**
     * Request Operation Refund
     *
     * @param float|null $amount
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    public function requestOperationRefund(float $amount = null)
    {
        return $this->_requestOperation(Operation::REFUND, $amount);
    }

    /**
     * Request Operation Cancel
     *
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    public function requestOperationCancel()
    {
        return $this->_requestOperation(Operation::CANCEL);
    }

    /**
     * Request Operation Accept Challenge
     *
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    public function requestOperationAcceptChallenge()
    {
        return $this->_requestOperation(Operation::ACCEPT_CHALLENGE);
    }

    /**
     * Request Operation Deny Challenge
     *
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    public function requestOperationDenyChallenge()
    {
        return $this->_requestOperation(Operation::DENY_CHALLENGE);
    }

    /**
     * Request Order Transaction Information
     *
     * @param string $orderId
     * @return array|Transaction[]|null
     */
    public function requestOrderTransactionInformation($orderId)
    {
        return $this->_gateway->requestOrderTransactionInformation($orderId) ?? null;
    }

    /**
     * Get Transaction Reference
     *
     * @param Order $order
     * @return string|null
     */
    public function getTransactionReference($order)
    {
        return ($transactions = $this->requestOrderTransactionInformation($order->getIncrementId()))
            ? $transactions[0]->getTransactionReference()
            : null;
    }

    /**
     * Request Security Settings
     *
     * @return mixed
     */
    public function requestSecuritySettings()
    {
        $securitySettings = $this->_gateway->requestSecuritySettings();
        return $securitySettings->getHashingAlgorithm();
    }

    /**
     * Clean the Transaction Value
     *
     * @param string|null $transactionReference
     * @return mixed|string
     */
    private function cleanTransactionValue(string $transactionReference)
    {
        list($tr) = explode("-", $transactionReference ?: '');
        return $tr;
    }

    /**
     * Get the Payment Method Request
     *
     * @return AbstractRequest|void
     */
    protected function _getPaymentMethodRequest()
    {
        $className = $this->_methodInstance->getConfigData('payment_method');
        if (!empty($className)) {
            return $this->_getRequestObject($className);
        }
    }

    /**
     * Convert the request object to an array
     *
     * @param  RequestInterface $request
     * @return array
     */
    protected function _requestToArray(RequestInterface $request)
    {
        return (new RequestSerializer($request))->toArray();
    }

    /**
     * Debug
     *
     * @param array $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        $this->_methodInstance->debugData($debugData);
    }

    /**
     * Get Payment
     *
     * @return false|float|DataObject|OrderPaymentInterface|mixed|null
     */
    protected function _getPayment()
    {
        return $this->_order->getPayment();
    }

    /**
     * Get Request Object
     *
     * @param string     $requestClassName
     * @param array|null $params
     * @return AbstractRequest
     */
    protected function _getRequestObject($requestClassName, array $params = null)
    {
        if ($params === null) {
            $params = $this->_getRequestParameters();
        }
        return $this->_requestFactory->create($requestClassName, $params)->getRequestObject();
    }

    /**
     * Get the Request Parameters
     *
     * @return array[]
     */
    protected function _getRequestParameters()
    {
        return [
            'params' => [
                'order' => $this->_order,
                'config' => $this->getConfiguration(),
            ],
        ];
    }

    /**
     * Request Operation
     *
     * @param  string      $operationType
     * @param  float|null  $amount
     * @param  string|null $operationId
     * @return \HiPay\Fullservice\Gateway\Model\Operation
     */
    protected function _requestOperation($operationType, $amount = null, $operationId = null)
    {
        $transactionReference = $this->cleanTransactionValue($this->_getPayment()->getCcTransId());
        if ($operationId === null) {
            $incrementTransaction = $this->countByTransactionsType($operationType, $this->_getPayment()->getId());
            $incrementTransaction++;
            $this->_getPayment()->setTransactionAdditionalInfo('increment_id', $incrementTransaction);
            $operationId = $this->_order->getIncrementId()
                . "-" . $operationType . "-manual-"
                . (int)$incrementTransaction;
        }

        $this->_getPayment()->setTransactionId($operationId);

        if ($operationType == Operation::REFUND) {
            $this->_getPayment()->getCreditMemo()->setTransactionId($operationId);
        }

        $params = $this->_getRequestParameters();
        $params['params']['operation'] = $operationType;
        $params['params']['paymentMethod'] = $this->_getPaymentMethodRequest();

        $maintenanceRequest = $this->_getRequestObject(
            \HiPay\FullserviceMagento\Model\Request\Maintenance::class,
            $params
        );
        $maintenanceRequest->operation_id = $operationId;
        $this->_debug($this->_requestToArray($maintenanceRequest));

        return $this->_gateway->requestMaintenanceOperation(
            $operationType,
            $transactionReference,
            $amount,
            $operationId,
            $maintenanceRequest
        );
    }

    /**
     * Count By Transactions Type
     *
     * @param  int $transactionType
     * @param  int $paymentId
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     */
    public function countByTransactionsType($transactionType, $paymentId)
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(\Magento\Sales\Api\Data\TransactionInterface::TXN_TYPE, $transactionType)
            ->addFilter(\Magento\Sales\Api\Data\TransactionInterface::PAYMENT_ID, $paymentId)
            ->create();

        return $this->_transactionRepositoryInterface->getList($searchCriteria)->getTotalCount();
    }

    /**
     * Request Payment Product
     *
     * @param array $paymentProduct
     * @param bool  $withOptions
     * @return array|AvailablePaymentProduct[]
     */
    public function requestPaymentProduct($paymentProduct = [], $withOptions = false)
    {
        $params = $this->_getRequestParameters();
        $params['params']['payment_product'] = $paymentProduct;
        $params['params']['with_options'] = $withOptions;
        $paymentProductRequest = $this->_getRequestObject(
            \HiPay\FullserviceMagento\Model\Request\Info\AvailablePaymentProduct::class,
            $params
        );

        return $this->_gateway->requestAvailablePaymentProduct(
            $paymentProductRequest
        );
    }
}
