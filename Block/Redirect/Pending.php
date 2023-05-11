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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
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
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_store;

    /**
     * Pending constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Framework\Locale\Resolver               $store
     * @param \Magento\Sales\Model\OrderFactory                $orderFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\Resolver $store,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_store = $store;
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
     *  Payment custom error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_checkoutSession->getErrorMessage();
    }

    /**
     * Returns locale code language of store
     */
    public function getLang()
    {
        return strtolower($this->_store->getLocale());
    }

    /**
     * Continue shopping URL
     *
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
            /** @var \Magento\Sales\Model\Order **/
            $order = $this->_orderFactory->create();
            $order->load($lastOrderId);
            $referenceToPay = $order->getPayment()->getAdditionalInformation('reference_to_pay');
            if ($referenceToPay) {
                $referenceToPay['method'] = $order->getPayment()->getCcType();
                if ($referenceToPay['method'] === 'multibanco') {
                    $referenceToPay['logo'] =
                    $this->getViewFileUrl('HiPay_FullserviceMagento::images/local/multibanco.png');
                    return $referenceToPay;
                } elseif ($referenceToPay['method'] === 'sisal') {
                    $referenceToPay['logo'] =
                    $this->getViewFileUrl('HiPay_FullserviceMagento::images/local/mooney.png');
                    return $referenceToPay;
                }
            }
        }
        return null;
    }
}
