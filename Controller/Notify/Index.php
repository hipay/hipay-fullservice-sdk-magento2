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

namespace HiPay\FullserviceMagento\Controller\Notify;

use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Notification\Factory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Webapi\Exception as WebApiException;
use Psr\Log\LoggerInterface;

/**
 * Notification controller
 * Manage order validation and modification
 *
 * Is protected by secret passphare (See \HiPay\FullserviceMagento\Observer\CheckHttpSignatureObserver.php)
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Index extends AppAction
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Config
     */
    protected $_hipayConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Factory
     */
    private $_notificationFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $hipayConfig,
        Factory $notificationFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
        $storeId = $this->_checkoutSession->getQuote()->getStore()->getStoreId();
        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);

        $this->_notificationFactory = $notificationFactory;
        $this->_logger = $logger;

        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof HttpRequest && $request->isPost()) {
                $request->setParam('isAjax', true);
                $request->getHeaders()->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
            }
        }
    }

    /**
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     **/
    public function execute()
    {
        $params = $this->getRequest()->getPost()->toArray();
        $reponseBody = 'OK';

        try {
            $cronModeActivated = $this->_hipayConfig->isNotificationCronActive();

            if ($cronModeActivated) {
                $notificationDate = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $params['date_updated']);
                $notificationData = [
                    'content' => json_encode($params),
                    'status' => $params['status'],
                    'hipay_created_at' => new \DateTime($notificationDate->format('Y-m-d H:i:s')),
                    'created_at' => new \DateTime()
                ];
                $notification = $this->_notificationFactory->create();
                $notification->setData($notificationData);
                $notification->save();

                $reponseBody = 'Notification will be processed later';
            } else {
                /**
                 * @var \HiPay\FullserviceMagento\Model\Notify $notify
                 **/
                $notify = $this->_objectManager->create(
                    '\HiPay\FullserviceMagento\Model\Notify',
                    ['params' => ['response' => $params]]
                );
                $notify->processTransaction();
            }
        } catch (WebApiException $e) {
            $this->_logger->warning($e);

            $this->getResponse()->setStatusHeader($e->getHttpCode());
            $this->getResponse()->setBody($e->getMessage())->sendResponse();
        } catch (\Exception $e) {
            $this->_logger->critical($e);

            $this->getResponse()->setStatusHeader(400, '1.1', $e->getMessage());
            $this->getResponse()->setBody($e->getTraceAsString())->sendResponse();
        }

        $this->getResponse()->setBody($reponseBody)->sendResponse();
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
