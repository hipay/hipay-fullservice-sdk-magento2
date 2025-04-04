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

namespace HiPay\FullserviceMagento\Model\Method;

use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use HiPay\FullserviceMagento\Model\Card;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

/**
 * Abstract Payment Method Class
 * All HiPay Fullservice payment methods inherit from her
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class FullserviceMethod extends AbstractMethod
{
    protected const SLEEP_TIME = 5;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapturePartial = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCaptureOnce = false;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = true;
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = [];
    /**
     * @var string[] keys to import in payment additional information
     */
    protected $_additionalInformationKeys = [
        'card_token',
        'create_oneclick',
        'eci',
        'cc_type',
        'fingerprint',
        'cc_owner',
        'card_pan',
        'card_expiry_month',
        'card_expiry_year',
        'card_multi_use',
        'browser_info'
    ];
    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
     */
    protected $_hipayConfig;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var string code
     */
    protected $_code;

    /**
     * @var string $_technicalCode
     */
    protected static $_technicalCode;

    /**
     * @var HiPay\FullserviceMagento\Model\Gateway\Factory
     */
    protected $_gatewayManagerFactory;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     * @var \HiPay\FullserviceMagento\Model\Email\Sender\FraudAcceptSender
     */
    protected $fraudAcceptSender;

    /**
     * @var \HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender
     */
    protected $fraudDenySender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \HiPay\FullserviceMagento\Model\CardFactory
     */
    protected $_cardFactory;

    /**
     * @var int
     */
    public $overridePendingTimeout = 30;

    /**
     * @var \HiPay\FullserviceMagento\Model\ResourceModel\Card\CollectionFactory;
     */
    protected $_cardCollectionFactory;

    /**
     * FullserviceMethod constructor.
     *
     * @param TransactionRepository                                        $transactionRepository
     * @param Method\Context                                               $context
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        \HiPay\FullserviceMagento\Model\Method\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context->getModelContext(),
            $context->getRegistry(),
            $context->getExtensionFactory(),
            $context->getCustomAttributeFactor(),
            $context->getPaymentData(),
            $context->getScopeConfig(),
            $context->getLogger(),
            $resource,
            $resourceCollection,
            $data
        );
        $this->transactionRepository = $transactionRepository;
        $this->_gatewayManagerFactory = $context->getGatewayManagerFactory();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->fraudAcceptSender = $context->getFraudAcceptSender();
        $this->fraudDenySender = $context->getFraudDenySender();
        $this->_hipayConfig = $context->getConfigFactory()->create(['params' => ['methodCode' => $this->getCode()]]);
        $this->_checkoutSession = $context->getCheckoutSession();
        $this->_cardFactory = $context->getCardFactory();
        $this->_cardCollectionFactory = $context->getCardCollectionFactory();
        $this->priceCurrency = $context->getPriceCurrency();

        $this->_debugReplacePrivateDataKeys = array('token', 'cardtoken', 'card_number', 'cvc');

        $sdkConfig = \HiPay\Fullservice\Data\PaymentProduct\Collection::getItem(static::$_technicalCode);

        if ($sdkConfig) {
            $this->_canCapture = $sdkConfig->getCanManualCapture();
            $this->_canCapturePartial = $sdkConfig->getCanManualCapturePartially();
            $this->_canRefund = $sdkConfig->getCanRefund();
            $this->_canRefundInvoicePartial = $sdkConfig->getCanRefundPartially();
        }
    }

    /**
     * Assign data to info model instance
     *
     * @param  array|\Magento\Framework\DataObject $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $additionalData = $data;
        if ($data->hasData(PaymentInterface::KEY_ADDITIONAL_DATA)) {
            $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
            if (!is_object($additionalData)) {
                $additionalData = new DataObject($additionalData ?: []);
            }
        }

        $this->_assignAdditionalInformation($additionalData);

        return $this;
    }

    protected function _assignAdditionalInformation(\Magento\Framework\DataObject $data)
    {
        $info = $this->getInfoInstance();
        foreach ($this->getAdditionalInformationKeys() as $key) {
            if ($data->getData($key) !== null) {
                $info->setAdditionalInformation($key, $data->getData($key));
            }
        }

        return $this;
    }

    protected function getAdditionalInformationKeys()
    {
        return $this->_additionalInformationKeys;
    }

    /**
     * Check method for processing with base currency
     *
     * @param  string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        $allowedCurrencies = explode(",", $this->getConfigData('allowed_currencies') ?: '');
        $currencyCode = $this->priceCurrency->getCurrency()->getData('currency_code') ?? $currencyCode;

        if ($this->getConfigData('allowed_currencies') != "") {
            return in_array($currencyCode, $allowedCurrencies);
        }
        return true;
    }

    /**
     * Whether this method can accept or deny payment
     *
     * @return bool
     * @api
     */
    public function canReviewPayment()
    {
        $orderCanReview = true;
        /**
         * @var $currentOrder \Magento\Sales\Model\Order
        */
        $currentOrder = $this->_registry->registry('current_order') ?: $this->_registry->registry(
            'hipay_current_order'
        );
        if ($currentOrder) {
            if (
                (int)$currentOrder->getPayment()->getAdditionalInformation('last_status')
                !== TransactionStatus::AUTHORIZED_AND_PENDING
            ) {
                $orderCanReview = false;
            }
        }

        return $orderCanReview && $this->_canReviewPayment;
    }

    /**
     * Is active
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->getConfigData('active', $storeId) && $this->_hipayConfig->hasCredentials();
    }

    /**
     * Mapper from HiPay-specific payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getConfigPaymentAction()
    {
        $action = $this->getConfigData('payment_action');
        switch ($action) {
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_AUTH:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
            case \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentActions::PAYMENT_ACTION_SALE:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
        }
        return $action;
    }

    /**
     * Capture payment method
     *
     * @param                                         \Magento\Framework\DataObject|InfoInterface $payment
     * @param                                         float                                       $amount
     * @return                                        $this
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);
        try {
            /**
             * @var \Magento\Sales\Model\Order\Payment $payment
            */
            if ($payment->getAuthorizationTransaction()) {  //Is not the first transaction
                $this->manualCapture($payment, $amount);
            } else { //Ok, it's the first transaction, so we request a new order (MO/TO)
                $this->place($payment);
            }
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(__('There was an error capturing the transaction: %1.', $e->getMessage()));
        }

        return $this;
    }

    protected function manualCapture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($this->isDifferentCurrency($payment)) {
            $amount = $payment->getTransactionAdditionalInfo()['invoice_capture']->getGrandTotal();
        }

        //Magento doesn't allow us to save object in transaction additional info
        $payment->setTransactionAdditionalInfo('invoice_capture', '');

        // As we already have a transaction reference, we can request a capture operation.
        $this->getGatewayManager($payment->getOrder())->requestOperationCapture($amount);
    }

    /**
     *
     * @param  \Magento\Sales\Model\Order $order
     * @param  array                      $params
     * @return \HiPay\FullserviceMagento\Model\Gateway\Manager
     */
    public function getGatewayManager($order, $params = [])
    {
        return $this->_gatewayManagerFactory->create($order, $params);
    }

    /**
     *  According the status provide a correct URL FOWARD
     *
     * @param  \HiPay\Fullservice\Gateway\Model\Transaction $response
     * @return string Redirect URL
     * @throws LocalizedException
     */
    protected function processResponse($response)
    {
        $successUrl = $this->urlBuilder->getUrl('hipay/redirect/accept', ['_secure' => true]);
        $pendingUrl = $this->urlBuilder->getUrl('hipay/redirect/pending', ['_secure' => true]);
        $forwardUrl = $response->getForwardUrl();
        $failUrl = $this->urlBuilder->getUrl('hipay/redirect/decline', ['_secure' => true]);
        $redirectUrl = $successUrl;
        switch ($response->getState()) {
            case TransactionState::COMPLETED:
                if (
                    $response->getPaymentMethod() &&
                    $response->getPaymentMethod()->getToken() &&
                    $this->_checkoutSession->getQuote()->getCustomerId()
                ) {
                    try {
                        $customerId = $this->_checkoutSession->getQuote()->getCustomerId();
                        $cardData = [
                            'card_token' => $response->getPaymentMethod()->getToken(),
                            'cc_type' => $response->getPaymentProduct(),
                            'card_expiry_month' => $response->getPaymentMethod()->getCardExpiryMonth(),
                            'card_expiry_year' => $response->getPaymentMethod()->getCardExpiryYear(),
                            'cc_owner' => $response->getPaymentMethod()->getCardHolder(),
                            'card_pan' => $response->getPaymentMethod()->getPan()
                        ];

                        $this->saveCard($cardData, $customerId, true);
                    } catch (\Exception $e) {
                        $this->_logger->error('Error saving card: ' . $e->getMessage());
                        // We don't want to interrupt the payment process if card saving fails
                    }
                }
                //redirectUrl is success by default
                break;
            case TransactionState::PENDING:
                $redirectUrl = $pendingUrl;
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason = $response->getReason();
                $this->_checkoutSession->setErrorMessage(
                    __('There was an error request new transaction: %1.', $reason['message'])
                );
                $redirectUrl = $failUrl;
                break;
            case TransactionState::ERROR:
                $reason = $response->getReason();
                $this->_checkoutSession->setErrorMessage(
                    __('There was an error request new transaction: %1.', $reason['message'])
                );
                throw new LocalizedException(__('There was an error request new transaction: %1.', $reason['message']));
            default:
                $redirectUrl = $failUrl;
        }

        return $redirectUrl;
    }

    public function place(\Magento\Payment\Model\InfoInterface $payment)
    {
        try {
            $response = $this->getGatewayManager($payment->getOrder())->requestNewOrder();

            // Set order state and status
            $payment->getOrder()->setState(\Magento\Sales\Model\Order::STATE_NEW);
            $payment->getOrder()->setStatus($this->getConfigData('order_status'));

            // Process response and store data
            $redirectUrl = $this->processResponse($response);
            $payment->setAdditionalInformation('response', $response->toArray());
            $payment->setAdditionalInformation('status', $response->getState());
            $payment->setAdditionalInformation('redirectUrl', $redirectUrl);
        } catch (\Exception $e) {
            if ($e->getCode() === 28) {
                $this->_logger->warning(sprintf(
                    'Exception occurred while requesting new transaction for order %s: %s',
                    $payment->getOrder()->getIncrementId(),
                    $e->getMessage()
                ));

                // Set order as pending
                $payment->getOrder()->setState(\Magento\Sales\Model\Order::STATE_NEW);
                $payment->getOrder()->setStatus($this->getConfigData('order_status'));

                // Set pending URL
                $pendingUrl = $this->urlBuilder->getUrl('hipay/redirect/pending', ['_secure' => true]);
                $payment->setAdditionalInformation('redirectUrl', $pendingUrl);

                return $this;
            }

            $this->_logger->critical($e);
            throw new LocalizedException(
                __('There was an error requesting new transaction: %1.', $e->getMessage())
            );
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param                                         \Magento\Framework\DataObject|InfoInterface $payment
     * @param                                         float                                       $amount
     * @return                                        $this
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($this->isDifferentCurrency($payment)) {
            $amount = $payment->formatAmount($payment->getCreditmemo()->getGrandTotal());
        }

        parent::refund($payment, $amount);

        $this->getGatewayManager($payment->getOrder())->requestOperationRefund($amount);

        //Set state to "OPEN" because we wait for notification with "REFUND" status
        $payment->getCreditmemo()->setState(Creditmemo::STATE_OPEN);

        $payment->getOrder()->save();

        /**
         * Fix for: TPPMAG2-64
         * Save creditmemo with a new state
         * Creditmemo repository object is not used because we want to save only the state
         * If we call Creditmemo repository save method, it's do a recall of process relation
         * and potentially cause an infinite loop
         *
         * @see https://github.com/magento/magento2/blob/2.1/app/code/Magento/Sales/Model/ResourceModel/Order/Creditmemo/Relation/Refund.php#L53
         */
        $payment->getCreditmemo()->save();

        return $this;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param                                         InfoInterface $payment
     * @return                                        false
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function acceptPayment(InfoInterface $payment)
    {
        parent::acceptPayment($payment);
        $this->getGatewayManager($payment->getOrder())->requestOperationAcceptChallenge();
        $this->fraudAcceptSender->send($payment->getOrder());
        return true;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param                                         InfoInterface $payment
     * @return                                        false
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function denyPayment(InfoInterface $payment)
    {
        parent::denyPayment($payment);
        $this->getGatewayManager($payment->getOrder())->requestOperationDenyChallenge();
        $this->fraudDenySender->send($payment->getOrder());
        return true;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();
        $cardToken = $info->getAdditionalInformation('card_token');
        $eci = $info->getAdditionalInformation('eci');
        if ($cardToken && $eci == 9) {
            //Check if current customer is owner of card token
            $card = $this->_cardFactory->create();
            $card->load($cardToken, 'cc_token');

            if (!$card->getId() || ($card->getCustomerId() != $this->_checkoutSession->getQuote()->getCustomerId())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Card does not exist!'));
            }

            //Set Card data to payment info
            $info->setCcType($card->getCcType())
                ->setCcOwner($card->getCcOwner())
                ->setCcLast4(substr($card->getCcNumberEnc() ?? '', -4))
                ->setCcExpMonth($card->getCcExpMonth())
                ->setCcExpYear($card->getCcExpYear())
                ->setCcNumEnc($card->getCcNumberEnc());
        }

        return $this;
    }

    /**
     * Wait for notification
     */
    protected function sleep()
    {
        sleep(self::SLEEP_TIME);
    }

    /**
     * @param  InfoInterface $payment
     * @return float
     */
    public function isDifferentCurrency(\Magento\Payment\Model\InfoInterface $payment)
    {
        $authTransac = $this->transactionRepository->getByTransactionType(
            \Magento\Sales\Api\Data\TransactionInterface::TYPE_AUTH,
            $payment->getId(),
            $payment->getOrder()->getId()
        );
        if (!$authTransac) {
            $authTransac = $this->transactionRepository->getByTransactionType(
                \Magento\Sales\Api\Data\TransactionInterface::TYPE_CAPTURE,
                $payment->getId(),
                $payment->getOrder()->getId()
            );
        }

        $transacCurrency = $authTransac->getAdditionalInformation('transac_currency');

        $isDifferentCurrency = $transacCurrency && $transacCurrency !== $payment->getOrder()->getBaseCurrencyCode();
        $isDifferentCurrency &= $transacCurrency === $payment->getOrder()->getOrderCurrencyCode();
        return $isDifferentCurrency;
    }

    /**
     * Save or update card
     *
     * @param array $cardData
     * @param int   $customerId
     * @param int   $authorized
     *
     * @return Card
     * @throws LocalizedException
     */
    protected function saveCard(array $cardData, int $customerId, bool $authorized = false)
    {
        try {
            // Format card name
            $cardName = $this->formatCardName($cardData);

            // Try to find existing card
            $card = $this->findExistingCard($customerId, $cardData['card_pan']);

            if (!$card) {
                $card = $this->_cardFactory->create();
            }

            // Set or update card data
            $card->setCustomerId($customerId)
                ->setName($cardName)
                ->setCcToken($cardData['card_token'])
                ->setCcType(strtolower($cardData['cc_type']))
                ->setCcExpMonth($cardData['card_expiry_month'])
                ->setCcExpYear($cardData['card_expiry_year'])
                ->setCcOwner($cardData['cc_owner'])
                ->setCcNumberEnc($cardData['card_pan'])
                ->setCclast4(substr($cardData['card_pan'], -4))
                ->setCcStatus(Card::STATUS_ENABLED)
                ->setIsDefault(0)
                ->setAuthorized($authorized);

            // Set created_at only for new cards
            if (!$card->getId()) {
                $card->setCreatedAt(new \DateTime());
            }

            // Save card
            $card->save();

            return $card;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Failed to save card: %1', $e->getMessage()));
        }
    }

    /**
     * Find existing card by customer ID and encrypted card number
     *
     * @param int    $customerId
     * @param string $ccNumberEnc
     * @return Card|null
     */
    protected function findExistingCard(int $customerId, string $ccNumberEnc)
    {
        $cardCollection = $this->_cardCollectionFactory->create();
        $cardCollection->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('cc_number_enc', $ccNumberEnc);

        return $cardCollection->getFirstItem()->getId() ? $cardCollection->getFirstItem() : null;
    }

    /**
     * Format card display name
     *
     * @param array $cardData
     * @return string
     */
    protected function formatCardName(array $cardData)
    {
        return sprintf(
            '%s •••• %s - Expires %s/%s',
            ucfirst(strtolower($cardData['cc_type'])),
            substr($cardData['card_pan'], -4),
            $cardData['card_expiry_month'],
            $cardData['card_expiry_year']
        );
    }
}
