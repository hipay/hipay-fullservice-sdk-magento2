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

namespace HiPay\FullserviceMagento\Model\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use HiPay\FullserviceMagento\Model\Email\Container\HostedPaymentLinkIdentity;

/**
 * Class Sender for Hosted link sended to customer in payment MO/TO (admin)
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HostedPaymentLinkSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @param Template                                              $templateContainer
     * @param HostedPaymentLinkIdentity                             $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface                              $logger
     * @param Renderer                                              $addressRenderer
     * @param PaymentHelper                                         $paymentHelper
     * @param OrderResource                                         $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $globalConfig
     * @param ManagerInterface                                      $eventManager
     */
    public function __construct(
        Template $templateContainer,
        HostedPaymentLinkIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->paymentHelper = $paymentHelper;
        $this->orderResource = $orderResource;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
    }

    /**
     * Sends link to hosted payment page.
     *
     * Email will be sent immediately
     *
     * @param  Order $order
     * @return bool
     */
    public function send(Order $order)
    {

        if ($this->checkAndSend($order)) {
            return true;
        }

        return false;
    }

    /**
     * Prepare email template with variables
     *
     * @param  Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'hostedRedirectUrl' => $order->getPayment()->getAdditionalInformation('redirectUrl'),
        ];

        $this->templateContainer->setTemplateVars($transport);

        parent::prepareTemplate($order);
    }

    /**
     * Get payment info block as html
     *
     * @param  Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
