<?php

namespace HiPay\FullserviceMagento\Model\Method;

use Magento\Framework\Exception\LocalizedException;
use HiPay\FullserviceMagento\Model\Gateway\Factory as GatewayManagerFactory;

/**
 * Abstract Method for API
 *
 * @author                                           Kassim Belghait <kassim@sirateck.com>
 * @copyright                                        Copyright (c) 2016 - HiPay
 * @license                                          http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link                                             https://github.com/hipay/hipay-fullservice-sdk-magento2
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractMethodAPI extends FullserviceMethod
{
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Instantiate state and set it to state object
     *
     * @param  string                        $paymentAction
     * @param  \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

        $this->_setHostedUrl($order);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function _setHostedUrl(\Magento\Sales\Model\Order $order)
    {
        $gateway = $this->_gatewayManagerFactory->create($order);
        $hppModel = $gateway->requestNewOrder();
        $redirectURL = $this->processResponse($hppModel);
        $order->getPayment()->setAdditionalInformation('redirectUrl', $redirectURL);
    }

    /**
     * Set initialization requirement state
     *
     * @param  bool $isInitializeNeeded
     * @return void
     */
    public function setIsInitializeNeeded($isInitializeNeeded = true)
    {
        $this->_isInitializeNeeded = (bool)$isInitializeNeeded;
    }

    /**
     * Get min and max amount by payment product
     *
     * @param $total
     * @param $technicalCode
     * @return bool
     */
    protected function getMinMaxByPaymentProduct($total, $technicalCode)
    {
        try {
            $availablePaymentProductResponse = $this->_gatewayManagerFactory->create(null, [
                    'apiEnv' => 1, 'storeId' => $this->_storeManager->getStore()->getId()
                ]);
            $paymentProducts = $availablePaymentProductResponse->requestPaymentProduct([$technicalCode], true);

            foreach ($paymentProducts as $product) {
                if ($product->getCode() === $technicalCode) {
                    $options = $product->getOptions();
                    $installments = substr($product->getCode(), -2, 1);
                    $minKey = "basketAmountMin{$installments}x";
                    $maxKey = "basketAmountMax{$installments}x";

                    if (isset($options[$minKey], $options[$maxKey])) {
                        return $total >= (float)$options[$minKey] && $total <= (float)$options[$maxKey];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return false;
        }

        return false;
    }
}
