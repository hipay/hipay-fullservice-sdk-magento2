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

namespace HiPay\FullserviceMagento\Observer;

use HiPay\Fullservice\Helper\Signature;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * HiPay module observer
 *
 * Check http signature from TPP notification
 *
 * Redirections haven't checked because http params can be not present (Depend of TPP config)
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CheckHttpSignatureObserver implements ObserverInterface
{
    private const SIGNATURE_HEADER = 'X-Allopass-Signature';
    private const HASH_SYNC_CACHE_PREFIX = 'hipay_hash_sync_';
    private const HASH_SYNC_LOCK_LIFETIME = 60;

    /**
     * @var array
     */
    protected $_actionsToCheck = [
        'hipay_notify_index'
    ];

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory $_orderFactory
     */
    protected $_orderFactory;

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
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $_logger;

    /**
     * @var CacheInterface
     */
    protected $_cache;

    /**
     * CheckHttpSignatureObserver constructor.
     *
     * @param \Magento\Sales\Model\OrderFactory     $orderFactory
     * @param ConfigFactory                         $configFactory
     * @param GatewayFactory                        $gatewayFactory
     * @param \Psr\Log\LoggerInterface              $logger
     * @param \HiPay\FullserviceMagento\Helper\Data $hipayHelper
     * @param CacheInterface                        $cache
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ConfigFactory $configFactory,
        GatewayFactory $gatewayFactory,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        CacheInterface $cache
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_configFactory = $configFactory;
        $this->_gatewayFactory = $gatewayFactory;
        $this->_hipayHelper = $hipayHelper;
        $this->_logger = $logger;
        $this->_cache = $cache;
    }

    /**
     * Check if signature and dispatch only if is valid
     *
     * @param  EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var $controller \HiPay\FullserviceMagento\Controller\Fullservice
        */
        $controller = $observer->getControllerAction();
        /**
         * @var $request \Magento\Framework\App\Request\Http
        */
        $request = $observer->getRequest();

        if (in_array($request->getFullActionName(), $this->_actionsToCheck)) {
            try {
                $orderId = $this->getOrderId($request);
                $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

                if (!$order->getId()) {
                    $this->_logger->warning(
                        'HiPay notification: order ID not found.',
                        ['order_id' => substr(preg_replace('/[\x00-\x1F\x7F]/', '', $orderId), 0, 64)]
                    );
                    $this->setPlainResponse($controller, 'Order ID not found.', 404);
                    return $this;
                }
                /**
                 * @var $config \HiPay\FullserviceMagento\Model\Config
                */
                $config = $this->_configFactory->create(
                    [
                        'params' => [
                            'methodCode' => $order->getPayment()->getMethod(),
                            'storeId' => $order->getStoreId(),
                            'order' => $order,
                            'forceMoto' => (bool)$order->getPayment()->getAdditionalInformation('is_moto')
                        ]
                    ]
                );
                $secretPassphrase = (string) $config->getSecretPassphrase();
                $signature = (string) $request->getHeader(self::SIGNATURE_HEADER);
                $payload = (string) $request->getContent();

                if ($secretPassphrase === '') {
                    $this->_logger->critical(
                        'HiPay notification rejected: secret passphrase is not configured for store '
                        . $order->getStoreId()
                    );
                    $this->setPlainResponse($controller, 'Wrong Secret Signature!', 500);
                    return $this;
                }

                if ($signature === '') {
                    $this->rejectSignature($controller, $order);
                    return $this;
                }

                $hash = $config->getHashingAlgorithm();
                if (!Signature::isValidHttpSignature($secretPassphrase, $hash, $signature, $payload)) {
                    $newHash = $this->syncHashAlgorithm(
                        $config,
                        $order,
                        $hash,
                        $secretPassphrase,
                        $signature,
                        $payload
                    );

                    if (strtoupper((string) $newHash) === strtoupper((string) $hash)) {
                        $this->rejectSignature($controller, $order);
                    } elseif (!Signature::isValidHttpSignature($secretPassphrase, $newHash, $signature, $payload)) {
                        $this->rejectSignature($controller, $order);
                    }
                }
            } catch (\Throwable $e) {
                $this->_logger->critical($e);
                $this->setPlainResponse($controller, 'Exception during check signature.', 500);
            }
        }

        return $this;
    }

    /**
     * Retry with the algorithm set in HiPay back office, called at most once a minute when nothing changed
     *
     * @param  \HiPay\FullserviceMagento\Model\Config $config
     * @param  \Magento\Sales\Model\Order             $order
     * @param  string                                 $hash
     * @param  string                                 $secretPassphrase
     * @param  string                                 $signature
     * @param  string                                 $payload
     * @return string
     * @throws LocalizedException
     */
    protected function syncHashAlgorithm($config, $order, $hash, $secretPassphrase, $signature, $payload)
    {
        if (Signature::isSameHashAlgorithm($secretPassphrase, $hash, $signature, $payload)) {
            return $hash;
        }

        $isMoto = (bool) $order->getPayment()->getAdditionalInformation('is_moto');
        $cacheKey = self::HASH_SYNC_CACHE_PREFIX . $order->getStoreId() . '_'
            . $order->getPayment()->getMethod() . '_' . (int) $isMoto;

        if ($this->_cache->load($cacheKey)) {
            return $hash;
        }

        $gatewayClient = $this->_gatewayFactory->create($order, ['forceMoto' => $isMoto]);

        try {
            $newHash = $this->_hipayHelper->updateHashAlgorithm($config, $gatewayClient, $order->getStore());
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error with retry hashing configuration .'), $e);
        }

        if (strtoupper((string) $newHash) === strtoupper((string) $hash)) {
            $this->_cache->save(
                '1',
                $cacheKey,
                [\Magento\Framework\App\Config::CACHE_TAG],
                self::HASH_SYNC_LOCK_LIFETIME
            );
        }

        return $newHash;
    }

    /**
     * Reject a notification whose signature is missing or invalid
     *
     * @param  \Magento\Framework\App\Action\Action $controller
     * @param  \Magento\Sales\Model\Order           $order
     * @return void
     */
    protected function rejectSignature($controller, $order)
    {
        $this->_logger->warning(
            'HiPay notification rejected: invalid signature.',
            ['order_id' => $order->getIncrementId(), 'store_id' => $order->getStoreId()]
        );
        $this->setPlainResponse($controller, 'Wrong Secret Signature!', 500);
    }

    /**
     * Stop dispatch and answer in plain text so the body is never read as HTML
     *
     * @param  \Magento\Framework\App\Action\Action $controller
     * @param  string                               $body
     * @param  int                                  $code
     * @return void
     */
    protected function setPlainResponse($controller, $body, $code)
    {
        $controller->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $controller->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
        $controller->getResponse()->setBody($body);
        $controller->getResponse()->setHttpResponseCode($code);
    }

    /**
     * Read the order increment ID from the notification body
     *
     * @param  \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    protected function getOrderId(\Magento\Framework\App\RequestInterface $request)
    {
        $order = $request->getPost('order', []);

        return is_array($order) && isset($order['id']) && is_scalar($order['id']) ? (string) $order['id'] : '';
    }
}
