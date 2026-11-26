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
use HiPay\FullserviceMagento\Model\NotificationFactory;
use HiPay\FullserviceMagento\Model\Notify;
use HiPay\FullserviceMagento\Model\NotifyFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Webapi\Exception as WebApiException;
use Psr\Log\LoggerInterface;

/**
 * Notification controller
 * Manage order validation and modification
 *
 * Is protected by secret passphare (See \HiPay\FullserviceMagento\Observer\CheckHttpSignatureObserver.php)
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Index extends AppAction implements CsrfAwareActionInterface
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
     * @var NotificationFactory
     */
    private $_notificationFactory;

    /**
     * @var NotifyFactory
     */
    private $_notifyFactory;

    /**
     * Index constructor.
     *
     * @param Context             $context
     * @param Session             $checkoutSession
     * @param Config              $hipayConfig
     * @param NotificationFactory $notificationFactory
     * @param NotifyFactory       $notifyFactory
     * @param LoggerInterface     $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $hipayConfig,
        NotificationFactory $notificationFactory,
        NotifyFactory $notifyFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
        $storeId = $this->_checkoutSession->getQuote()->getStore()->getStoreId();
        $this->_hipayConfig = $hipayConfig;
        $this->_hipayConfig->setStoreId($storeId);

        $this->_notificationFactory = $notificationFactory;
        $this->_notifyFactory = $notifyFactory;
        $this->_logger = $logger;
    }

    /**
     * Server to server call, no form key: protected by the HiPay signature
     *
     * @param  RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Skip the form key check, the signature is checked by the observer
     *
     * @param  RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Process the HiPay notification, or save it for the cron when the cron mode is enabled
     *
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
                    'order_id' => $params['order']['id'],
                    'hipay_created_at' => new \DateTime($notificationDate->format('Y-m-d H:i:s')),
                    'created_at' => new \DateTime()
                ];
                $notification = $this->_notificationFactory->create();
                $notification->setData($notificationData);
                $notification->save();

                $reponseBody = 'Notification will be processed later';
            } else {
                /**
                 * @var Notify $notify
                 **/
                $notify = $this->_notifyFactory->create(
                    ['params' => ['response' => $params]]
                );
                $notify->processTransaction();
                if ($notify->getResponseMessage()) {
                    $reponseBody = $notify->getResponseMessage();
                }
            }
        } catch (WebApiException $e) {
            $this->_logger->warning($e);

            $this->getResponse()->setStatusHeader($e->getHttpCode());
            $this->getResponse()->setBody($e->getMessage())->sendResponse();
        } catch (\Exception $e) {
            $this->_logger->critical($e);

            $this->getResponse()->setStatusHeader(400);
            $this->getResponse()->setBody('Error while processing the notification.')->sendResponse();
        }

        $this->getResponse()->setBody($reponseBody)->sendResponse();
    }

    /**
     * Retrieve request object
     *
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return Http
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
