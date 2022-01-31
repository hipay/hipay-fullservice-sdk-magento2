<?php

/**
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request\ThreeDS;

use HiPay\Fullservice\Enum\ThreeDSTwo\NameIndicator;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Payment as PaymentInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Purchase as PurchaseInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Shipping as ShippingInfo;
use HiPay\FullserviceMagento\Model\Request\AbstractRequest;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Customer as CustomerInfo;

/**
 *
 * @author HiPay <support@hipay.com>
 * @copyright Copyright (c) 2019 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AccountInfoFormatter extends AbstractRequest
{
    /**
     * @var \HiPay\FullserviceMagento\Helper\ThreeDSTwo
     */
    protected $_threeDSHelper;

    /**
     * @var \HiPay\FullserviceMagento\Model\CardFactory
     */
    protected $_cardFactory;

    /**
     * AccountInfoFormatter constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \HiPay\FullserviceMagento\Helper\Data $helper
     * @param \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper
     * @param \HiPay\FullserviceMagento\Model\CardFactory $cardFactory
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        \HiPay\FullserviceMagento\Helper\ThreeDSTwo $threeDSHelper,
        \HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
        $params = []
    ) {
        parent::__construct(
            $logger,
            $checkoutData,
            $customerSession,
            $checkoutSession,
            $localeResolver,
            $requestFactory,
            $urlBuilder,
            $helper,
            $params
        );

        $this->_threeDSHelper = $threeDSHelper;
        $this->_cardFactory = $cardFactory;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @return AccountInfo
     * @throws \Exception
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::mapRequest()
     */
    protected function mapRequest()
    {
        $accountInfo = new AccountInfo();

        $accountInfo->customer = $this->getCustomerInfo();
        $accountInfo->purchase = $this->getPurchaseInfo();
        $accountInfo->payment = $this->getPaymentInfo();
        $accountInfo->shipping = $this->getShippingInfo();

        return $accountInfo;
    }

    /**
     * @return CustomerInfo
     */
    protected function getCustomerInfo()
    {
        $customerInfo = new CustomerInfo();

        if ($this->_threeDSHelper->isCustomerLoggedIn()) {
            $accountChange = $this->_customerSession->getCustomer()->getData('updated_at');
            $rpTokenCreatedAt = $this->_customerSession->getCustomer()->getData('rp_token_created_at');
            $timestampCreated = $this->_customerSession->getCustomer()->getCreatedAtTimestamp();

            $customerInfo->account_change = (int)date('Ymd', strtotime($accountChange));
            $customerInfo->opening_account_date = (int)date('Ymd', $timestampCreated);
            $customerInfo->password_change = (int)date('Ymd', strtotime($rpTokenCreatedAt));
        }

        return $customerInfo;
    }

    /**
     * @return PurchaseInfo
     * @throws \Exception
     */
    protected function getPurchaseInfo()
    {
        $purchaseInfo = new PurchaseInfo();

        if ($this->_threeDSHelper->isCustomerLoggedIn()) {
            $sixMonthAgo = new \DateTime('6 months ago');
            $twentyFourHoursAgo = new \DateTime('24 hours ago');
            $oneYearAgo = new \DateTime('1 years ago');

            $purchaseInfo->count = $this->_threeDSHelper->getNbCustomerOrder(
                $this->_customerId,
                $this->getStoreId(),
                $sixMonthAgo
            );
            $purchaseInfo->payment_attempts_24h = $this->_threeDSHelper->getNbCustomerOrder(
                $this->_customerId,
                $this->getStoreId(),
                $twentyFourHoursAgo
            );
            $purchaseInfo->payment_attempts_1y = $this->_threeDSHelper->getNbCustomerOrder(
                $this->_customerId,
                $this->getStoreId(),
                $oneYearAgo
            );
            $purchaseInfo->card_stored_24h = $this->_threeDSHelper->getNbOneclickAttempt(
                $this->_customerId,
                $this->getStoreId(),
                $twentyFourHoursAgo
            );
        }

        return $purchaseInfo;
    }

    /**
     * @return PaymentInfo
     */
    protected function getPaymentInfo()
    {
        $paymentInfo = new PaymentInfo();

        if ($this->_threeDSHelper->isCustomerLoggedIn() && $this->_threeDSHelper->isRecurring($this->_checkoutData)) {
            $card = $this->_cardFactory->create();
            $card->getResource()->load($card, $this->getCardToken(), 'cc_token');

            if ($card->getId()) {
                $paymentInfo->enrollment_date = (int)date('Ymd', strtotime($card->getCreatedAt()));
            }
        }

        return $paymentInfo;
    }

    /**
     * @return ShippingInfo
     */
    protected function getShippingInfo()
    {
        $shippingInfo = new ShippingInfo();

        if ($this->_threeDSHelper->isCustomerLoggedIn()) {
            $shippingInfo->shipping_used_date = $this->_threeDSHelper->getDateAddressFirstUsed(
                $this->getCustomerAddressId(),
                $this->_customerId,
                $this->getStoreId()
            );

            $shippingInfo->name_indicator = NameIndicator::DIFFERENT;

            if ($this->_threeDSHelper->isIdenticalShippingName($this->_checkoutData->getQuote())) {
                $shippingInfo->name_indicator = NameIndicator::IDENTICAL;
            }
        }

        return $shippingInfo;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->_checkoutData->getQuote()->getStoreId();
    }

    /**
     * @return string|null
     */
    private function getCardToken()
    {
        return $this->_checkoutData->getQuote()->getPayment()->getAdditionalInformation('card_token');
    }

    /**
     * @return int|null
     */
    private function getCustomerAddressId()
    {
        return $this->_checkoutData->getQuote()->getShippingAddress()->getCustomerAddressId();
    }
}
