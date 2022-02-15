<?php
/**
 * HiPay fullservice SDK
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

namespace HiPay\FullserviceMagento\Block\Redirect;

class Pending extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Pending constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Order ID
     * @return string
     */
    public function getRealOrderId()
    {
        return $this->_checkoutSession->getLastRealOrderId();
    }

    /**
     * Payment custom error message
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_checkoutSession->getErrorMessage();
    }

    /**
     * Continue shopping URL
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart');
    }

    public function getReferenceToPay()
    {
        $lastOrderId = $this->_checkoutSession->getLastOrderId();
        if ($lastOrderId) {
            /** @var $order  \Magento\Sales\Model\Order **/
            $order = $this->_orderFactory->create();
            $order->getResource()->load($order, $lastOrderId);
            $referenceToPay = $order->getPayment()->getAdditionalInformation('reference_to_pay');
            if ($order->getPayment()->getCcType() === 'multibanco' && $referenceToPay) {
                $referenceToPay['logo'] = $this->getViewFileUrl('HiPay_FullserviceMagento::images/local/multibanco.png');
                return $referenceToPay;
            }
        }
        return null;
    }
}
