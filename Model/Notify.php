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

namespace HiPay\FullserviceMagento\Model;

use HiPay\Fullservice\Enum\Transaction\Operation;
use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\Fullservice\Gateway\Mapper\TransactionMapper;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudReviewSender;
use HiPay\FullserviceMagento\Model\Email\Sender\FraudDenySender;
use Magento\Framework\Webapi\Exception as WebApiException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\FullserviceMagento\Model\Method\HostedMethod;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;
use Magento\Sales\Model\OrderRepository;

/**
 * Notify Class Model
 *
 * Proceed all notifications
 * In construct method Order Model is loaded and Transation Model (SDK) is created
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Notify
{
    /**
     *
     * @var \Magento\Sales\Model\OrderFactory $_orderFactory
     */
    protected $_orderFactory;

    /**
     * @var FraudReviewSender
     */
    protected $fraudReviewSender;

    /**
     * @var FraudDenySender
     */
    protected $fraudDenySender;

    /**
     *
     * @var OrderSender $orderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     *
     * @var Transaction $_transaction Response Model Transaction
     */
    protected $_transaction;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\FullserviceMethod $_methodInstance
     */
    protected $_methodInstance;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\CardFactory $_cardFactory
     */
    protected $_cardFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory
     */
    protected $ppFactory;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory
     */
    protected $spFactory;

    /**
     *
     * @var bool $isSplitPayment
     */
    protected $isSplitPayment = false;

    /**
     *
     * @var bool $isFirstSplitPayment
     */
    protected $isFirstSplitPayment = false;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\SplitPayment $splitPayment
     */
    protected $splitPayment;

    /**
     * @var ResourceOrder $orderResource
     */
    protected $orderResource;

    /**
     * @var \Magento\Framework\DB\Transaction $transactionDB
     */
    protected $_transactionDB;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender TransactionRepository
     */
    protected $creditmemoSender;

    /**
     * @var UpdateCouponUsages
     */
    protected $updateCouponUsages;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \HiPay\FullserviceMagento\Model\CardFactory $cardFactory,
        OrderSender $orderSender,
        FraudReviewSender $fraudReviewSender,
        FraudDenySender $fraudDenySender,
        \Magento\Payment\Helper\Data $paymentHelper,
        \HiPay\FullserviceMagento\Model\PaymentProfileFactory $ppFactory,
        \HiPay\FullserviceMagento\Model\SplitPaymentFactory $spFactory,
        ResourceOrder $orderResource,
        \Magento\Framework\DB\Transaction $_transactionDB,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        UpdateCouponUsages $updateCouponUsages,
        OrderRepository $orderRepository,
        $params = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_cardFactory = $cardFactory;
        $this->orderSender = $orderSender;
        $this->fraudReviewSender = $fraudReviewSender;
        $this->fraudDenySender = $fraudDenySender;

        $this->ppFactory = $ppFactory;
        $this->spFactory = $spFactory;

        $this->orderResource = $orderResource;
        $this->_transactionDB = $_transactionDB;
        $this->priceCurrency = $priceCurrency;
        $this->transactionRepository = $transactionRepository;

        $this->creditmemoSender = $creditmemoSender;

        $this->updateCouponUsages = $updateCouponUsages;
        $this->orderRepository = $orderRepository;

        if (isset($params['response']) && is_array($params['response'])) {
            $incrementId = $params['response']['order']['id'];
            if (strpos($incrementId, '-split-') !== false) {
                list($realIncrementId, , $splitPaymentId) = explode("-", $incrementId ?: '');
                $params['response']['order']['id'] = $realIncrementId;
                $this->isSplitPayment = true;
                $this->splitPayment = $this->spFactory->create();
                $this->splitPayment->load((int)$splitPaymentId);

                if (!$this->splitPayment->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(sprintf('Wrong Split Payment ID: "%s".', $splitPaymentId))
                    );
                }
            }

            $this->_transaction = (new TransactionMapper($params['response']))->getModelObjectMapped();

            $this->_order = $this->_orderFactory->create()->loadByIncrementId($this->_transaction->getOrder()->getId());

            if (!$this->_order->getId()) {
                throw new WebApiException(
                    __(sprintf('Order ID not found: "%s".', $this->_transaction->getOrder()->getId())),
                    0,
                    WebApiException::HTTP_NOT_FOUND
                );
            }

            if ($this->_order->getPayment()->getAdditionalInformation('profile_id') && !$this->isSplitPayment) {
                $this->isFirstSplitPayment = true;
            }

            //Retieve method model
            $this->_methodInstance = $paymentHelper->getMethodInstance($this->_order->getPayment()->getMethod());

            //Debug transaction notification if debug enabled
            $this->_methodInstance->debugData($this->_transaction->toArray());
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Posted data response as array is required.')
            );
        }
    }

    public function processSplitPayment()
    {
        $amount = $this->_order->getOrderCurrency()->formatPrecision(
            $this->splitPayment->getAmountToPay(),
            2,
            [],
            false
        );
        $this->_doTransactionMessage(
            __('Split Payment #%1. %2 %3.', $this->splitPayment->getId(), $amount, $this->_transaction->getMessage())
        );
        return $this;
    }

    protected function canProcessTransaction()
    {
        $canProcess = false;

        switch ($this->_transaction->getStatus()) {
            case TransactionStatus::EXPIRED:
                // status : 114
                if (
                    in_array(
                        $this->_order->getStatus(),
                        array(Config::STATUS_AUTHORIZED, Config::STATUS_AUTHORIZATION_REQUESTED)
                    )
                ) {
                    $canProcess = true;
                } else {
                    $savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
                    throw new WebApiException(
                        __(
                            'Cannot process transaction for order "%1". State: "%2". Status: "%3". Status history : %4',
                            $this->_transaction->getOrder()->getId(),
                            $this->_order->getState(),
                            $this->_order->getStatus(),
                            is_array($savedStatues) ? implode(' - ', $savedStatues) : ''
                        ),
                        0,
                        WebApiException::HTTP_BAD_REQUEST
                    );
                }
                break;
            case TransactionStatus::AUTHORIZED:
                // status : 116
                if (
                    $this->_order->getState() == \Magento\Sales\Model\Order::STATE_NEW
                    || $this->_order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
                    || $this->_order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
                    || in_array($this->_order->getStatus(), array(Config::STATUS_AUTHORIZATION_REQUESTED))
                ) {
                    $canProcess = true;
                } else {
                    $savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
                    throw new WebApiException(
                        __(
                            'Cannot process transaction for order "%1". State: "%2". Status: "%3". Status history : %4',
                            $this->_transaction->getOrder()->getId(),
                            $this->_order->getState(),
                            $this->_order->getStatus(),
                            is_array($savedStatues) ? implode(' - ', $savedStatues) : ''
                        ),
                        0,
                        WebApiException::HTTP_BAD_REQUEST
                    );
                }
                break;
            case TransactionStatus::CAPTURE_REQUESTED:
            case TransactionStatus::CAPTURED:
                // if operation ID exists matching magento2, check invoice related to this order
                // then, if invoice does not exist ~> refuse notif
                $operationId = $this->_transaction->getOperation()
                    ? $this->_transaction->getOperation()->getId()
                    : null;
                if (
                        $operationId
                        && preg_match("/-" . Operation::CAPTURE . "-manual-/", $operationId)
                        && !$this->getInvoiceForTransactionId($this->_order, $operationId)
                ) {
                    throw new WebApiException(
                        __(sprintf('Invoice "%s" does not exist in database.', $operationId)),
                        0,
                        WebApiException::HTTP_BAD_REQUEST
                    );
                }

                // status : 118 - We check the 116 has been received before handling
                $savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
                if (is_array($savedStatues) && isset($savedStatues[TransactionStatus::AUTHORIZED])) {
                    $canProcess = true;
                } else {
                    throw new WebApiException(
                        __(sprintf('Order "%s" was not authorized.', $this->_transaction->getOrder()->getId())),
                        0,
                        WebApiException::HTTP_BAD_REQUEST
                    );
                }

                // status : 117
                if (
                    $this->_transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED
                    && $this->_order->hasInvoices()
                    && $this->_order->getBaseTotalDue() != $this->_order->getBaseGrandTotal()
                ) {
                    $canProcess = false;
                }

                break;
            default:
                $canProcess = true;
                break;
        }

        return $canProcess;
    }

    public function processTransaction()
    {
        if ($this->isSplitPayment) {
            $this->processSplitPayment();
            return $this;
        }

        if (!$this->canProcessTransaction()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(sprintf(
                    'Cannot process notification "%s" for transaction "%s".',
                    $this->_transaction->getStatus(),
                    $this->_transaction->getOrder()->getId()
                ))
            );
        }

        /**
         * Begin transaction to lock this order record during update
         */
        $this->orderResource->getConnection()->beginTransaction();

        $selectForupdate = $this->orderResource->getConnection()->select()
            ->from($this->orderResource->getMainTable())->where(
                $this->orderResource->getIdFieldName() . '=?',
                $this->_order->getId()
            )
            ->forUpdate(true);

        //Execute for update query
        $this->orderResource->getConnection()->fetchOne($selectForupdate);

        //Write about notification in order history
        $this->_doTransactionMessage("Status code: " . $this->_transaction->getStatus());

        // Write CC TYPE if Payment is Hosted Payment
        if (empty($this->_order->getPayment()->getCcType())) {
            $this->_order->getPayment()->setCcType($this->_transaction->getPaymentProduct());
        }

        switch ($this->_transaction->getStatus()) {
            case TransactionStatus::BLOCKED:
                // status : 110
                $this->_setFraudDetected();
                // no break
            case TransactionStatus::DENIED:
                // status : 111
                $this->_doTransactionDenied();
                break;
            case TransactionStatus::AUTHORIZED_AND_PENDING:
                // status : 112
            case TransactionStatus::PENDING_PAYMENT:
                // status : 200
                $this->_setFraudDetected();
                $this->_doTransactionAuthorizedAndPending();
                break;
            case TransactionStatus::AUTHORIZATION_REQUESTED:
                // status : 142
                $this->_changeStatus(Config::STATUS_AUTHORIZATION_REQUESTED);
                break;
            case TransactionStatus::REFUSED:
                // status 113 : Fail transaction if not via hosted page
                if ($this->_order->getPayment()->getMethodInstance() instanceof HostedMethod) {
                    $this->_doTransactionMessage();
                } else {
                    $this->_doTransactionFailure();
                }
                break;
            case TransactionStatus::CANCELLED:
                //115 Cancel order and transaction
            case TransactionStatus::AUTHORIZATION_REFUSED:
                // status : 163
                $this->_doTransactionFailure();
                break;
            case TransactionStatus::CAPTURE_REFUSED:
                // status : 173
                $this->_doTransactionCaptureRefused();
                break;
            case TransactionStatus::EXPIRED: //114 Hold order, the merchant can unhold and try a new capture
                $this->_doTransactionFailure();
                break;
            case TransactionStatus::AUTHORIZED:
                // status : 116
                $this->_doTransactionAuthorization();
                break;
            case TransactionStatus::CAPTURE_REQUESTED:
                // status : 117
                $this->_doTransactionCaptureRequested();
                //If status Capture Requested is not configured to validate the order, we break.
                if (
                    (int)$this->_order->getPayment()
                        ->getMethodInstance()
                        ->getConfigData('hipay_status_validate_order') != 117
                ) {
                    break;
                }
                // no break
            case TransactionStatus::CAPTURED:
                // status : 118
            case TransactionStatus::PARTIALLY_CAPTURED:
                // status : 119
                //If status Capture Requested is configured to validate the order and is a direct capture notification
                // (118), we break because order is already validate.
                if (
                    (int)$this->_order->getPayment()->getMethodInstance()->getConfigData('hipay_status_validate_order')
                    == 117
                    && (int)$this->_transaction->getStatus() == 118
                    && !in_array(strtolower($this->_order->getPayment()->getCcType()), array('amex', 'ae'))
                ) {
                    break;
                }

                // Skip magento fraud checking
                $this->_doTransactionCapture(true);

                /**
                 * save split payments
                 */
                if (!$this->orderAlreadySplit()) {
                    $this->insertSplitPayment();
                }

                break;
            case TransactionStatus::REFUND_REQUESTED:
                // status : 124
                $this->_doTransactionRefundRequested();
                break;
            case TransactionStatus::REFUNDED:
                // status : 125
            case TransactionStatus::PARTIALLY_REFUNDED:
                // status : 126
                $this->_doTransactionRefund();
                break;
            case TransactionStatus::REFUND_REFUSED:
                // status : 165
                $this->_doTransactionRefundRefused();
                // no break
            case TransactionStatus::CREATED:
                // status : 101
            case TransactionStatus::CARD_HOLDER_ENROLLED:
                // status : 103
            case TransactionStatus::CARD_HOLDER_NOT_ENROLLED:
                // status : 104
            case TransactionStatus::UNABLE_TO_AUTHENTICATE:
                // status : 105
            case TransactionStatus::CARD_HOLDER_AUTHENTICATED:
                // status : 106
            case TransactionStatus::AUTHENTICATION_ATTEMPTED:
                // status : 107
            case TransactionStatus::COULD_NOT_AUTHENTICATE:
                // status : 108
            case TransactionStatus::AUTHENTICATION_FAILED:
                // status : 109
            case TransactionStatus::COLLECTED:
                // status : 120
            case TransactionStatus::PARTIALLY_COLLECTED:
                // status : 121
            case TransactionStatus::SETTLED:
                // status : 122
            case TransactionStatus::PARTIALLY_SETTLED:
                // status : 123
            case TransactionStatus::CHARGED_BACK:
                // status : 129
            case TransactionStatus::DEBITED:
                // status : 131
            case TransactionStatus::PARTIALLY_DEBITED:
                // status : 132
            case TransactionStatus::AUTHENTICATION_REQUESTED:
                // status : 140
            case TransactionStatus::AUTHENTICATED:
                // status : 141
            case TransactionStatus::ACQUIRER_FOUND:
                // status : 150
            case TransactionStatus::ACQUIRER_NOT_FOUND:
                // status : 151
            case TransactionStatus::CARD_HOLDER_ENROLLMENT_UNKNOWN:
                // status : 160
            case TransactionStatus::RISK_ACCEPTED:
                // status : 161
                $this->_doTransactionMessage();
                break;
        }

        if (
            $this->_transaction->getStatus() == TransactionStatus::CAPTURED
            || $this->_transaction->getStatus() == TransactionStatus::AUTHORIZED
        ) {
            /**
             * save token and credit card informations encryted
             */
            $this->_saveCc();
        }

        //Save status infos
        $this->saveHiPayStatus();

        //Send commit to unlock order table
        $this->orderResource->getConnection()->commit();

        return $this;
    }

    /**
     * Save infos of statues processed
     */
    protected function saveHiPayStatus()
    {
        $lastStatus = $this->_transaction->getStatus();
        $savedStatues = $this->_order->getPayment()->getAdditionalInformation('saved_statues');
        if (!is_array($savedStatues)) {
            $savedStatues = [];
        }

        if (isset($savedStatues[$lastStatus])) {
            return;
        }

        $savedStatues[$lastStatus] = [
            'saved_at' => new \DateTime(),
            'state' => $this->_transaction->getState(),
            'status' => $lastStatus
        ];

        //Save array of statues already processed
        $this->_order->getPayment()->setAdditionalInformation('saved_statues', $savedStatues);

        //Save the last status
        $this->_order->getPayment()->setAdditionalInformation('last_status', $lastStatus);
        $this->_order->save();
    }

    protected function orderAlreadySplit()
    {
        /**
         * @var $splitPayments \HiPay\FullserviceMagento\Model\ResourceModel\SplitPayment\Collection
         */
        $splitPayments = $this->spFactory->create()->getCollection()->addFieldToFilter(
            'order_id',
            $this->_order->getId()
        );
        if ($splitPayments->count()) {
            return true;
        }
        return false;
    }

    protected function insertSplitPayment()
    {
        //Check if it is split payment and insert it
        if (($profileId = (int)$this->_order->getPayment()->getAdditionalInformation('profile_id'))) {
            $profile = $this->ppFactory->create();
            $profile->load($profileId);
            if ($profile->getId()) {
                $amount = $this->_order->getBaseGrandTotal();
                if ($this->_transaction->getCurrency() != $this->_order->getBaseCurrencyCode()) {
                    $amount = $this->_order->getGrandTotal();
                }

                $orderCreatedAt = new \DateTime($this->_order->getCreatedAt() ?: '');

                $splitAmounts = $profile->splitAmount($amount, $orderCreatedAt);
                $splitAmountsCount = count($splitAmounts);

                /**
                 * @var $splitPayment \HiPay\FullserviceMagento\Model\SplitPayment
                */
                for ($i = 0; $i < $splitAmountsCount; $i++) {
                    $splitPayment = $this->spFactory->create();

                    $splitPayment->setAmountToPay($splitAmounts[$i]['amountToPay']);
                    $splitPayment->setAttempts($i == 0 ? 1 : 0);
                    $splitPayment->setCardToken($this->_transaction->getPaymentMethod()->getToken());
                    $splitPayment->setCustomerId($this->_order->getCustomerId());
                    $splitPayment->setDateToPay($splitAmounts[$i]['dateToPay']);
                    $splitPayment->setMethodCode($this->_order->getPayment()->getMethod());
                    $splitPayment->setRealOrderId($this->_order->getIncrementId());
                    $splitPayment->setOrderId($this->_order->getId());
                    $splitPayment->setStatus(
                        $i == 0 ? SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE :
                            SplitPayment::SPLIT_PAYMENT_STATUS_PENDING
                    );
                    $splitPayment->setProfileId($profileId);
                    if (
                        $this->_transaction->getCurrency() != $this->_order->getBaseCurrencyCode()
                    ) {
                        $splitPayment->setBaseGrandTotal($this->_order->getGrandTotal());
                        $splitPayment->setBaseCurrencyCode($this->_transaction->getCurrency());
                    } else {
                        $splitPayment->setBaseGrandTotal($this->_order->getBaseGrandTotal());
                        $splitPayment->setBaseCurrencyCode($this->_order->getBaseCurrencyCode());
                    }

                    try {
                        $splitPayment->save();
                    } catch (\Exception $e) {
                        if ($this->_order->canHold()) {
                            $this->_order->hold();
                        }
                        $this->_doTransactionMessage(
                            __('Order held because an error occurred while saving one of the split payments')
                        );
                    }
                }
            } else {
                if ($this->_order->canHold()) {
                    $this->_order->hold();
                }
                $this->_doTransactionMessage(__('Order held because split payments was not saved!'));
            }
        }
    }

    protected function _canSaveCc()
    {
        return (bool)in_array(
            $this->_transaction->getPaymentProduct(),
            ['visa', 'american-express', 'mastercard', 'cb']
        )
            && $this->_order->getPayment()->getAdditionalInformation('create_oneclick');
    }

    /**
     * @return bool|\HiPay\FullserviceMagento\Model\Card
     */
    protected function _saveCc()
    {
        if ($this->_canSaveCc()) {
            $token = $this->_transaction->getPaymentMethod()->getToken();
            if (!$this->_cardTokenExist($token)) {
                /**
                 * @var $card \HiPay\FullserviceMagento\Model\Card
                 */
                $card = $this->_cardFactory->create();
                /**
                 * @var $paymentMethod \HiPay\Fullservice\Gateway\Model\PaymentMethod
                 */
                $paymentMethod = $this->_transaction->getPaymentMethod();
                $paymentProduct = $this->_transaction->getPaymentProduct();
                $card->setCcToken($token);
                $card->setCustomerId($this->_order->getCustomerId());
                $card->setCcExpMonth($paymentMethod->getCardExpiryMonth());
                $card->setCcExpYear($paymentMethod->getCardExpiryYear());
                $card->setCcNumberEnc($paymentMethod->getPan());
                $card->setCcType($paymentProduct);
                $card->setCcOwner($paymentMethod->getCardHolder());
                $card->setCcStatus(\HiPay\FullserviceMagento\Model\Card::STATUS_ENABLED);
                $card->setName(sprintf(__('Card %s - %s'), $paymentMethod->getBrand(), $paymentMethod->getPan()));
                $card->setCreatedAt(new \DateTime());

                try {
                    return $card->save();
                } catch (\Exception $e) {
                    $this->_generateComment(__("Card not registered! Due to: %s", $e->getMessage()), true);
                }
            }
        }

        return false;
    }

    protected function _cardTokenExist($token)
    {
        $card = $this->_cardFactory->create();
        $card->load($token, 'cc_token');
        return (bool)$card->getId();
    }

    /**
     * Check Fraud Screenig result for fraud detection
     */
    protected function _setFraudDetected()
    {
        if (($fraudSreening = $this->_transaction->getFraudScreening()) !== null) {
            if ($fraudSreening->getResult()) {
                $payment = $this->_order->getPayment();
                $payment->setIsFraudDetected(true);

                $payment->setAdditionalInformation('fraud_type', $fraudSreening->getResult());
                $payment->setAdditionalInformation('fraud_score', $fraudSreening->getScoring());
                $payment->setAdditionalInformation('fraud_review', $fraudSreening->getReview());

                $isDeny = ($fraudSreening->getResult() != 'challenged'
                    || $this->_transaction->getState() == TransactionState::DECLINED);

                if (!$isDeny) {
                    $this->fraudReviewSender->send($this->_order);
                } else {
                    $this->fraudDenySender->send($this->_order);
                }
            }
        }
    }

    protected function _changeStatus($status, $comment = "", $addToHistory = true, $save = true)
    {
        $this->_generateComment($comment, $addToHistory);
        $this->_order->setStatus($status);

        if ($save) {
            $this->_order->save();
        }
    }

    /**
     * Add status to order history
     *
     * @param string $message
     */
    protected function _doTransactionMessage($message = "")
    {
        if ($this->_transaction->getReason() != "") {
            $message .= __(" Reason: %1", $this->_transaction->getReason());
        }
        $this->_generateComment($message, true);
        $this->_order->save();
    }

    /**
     * Process a refund
     *
     * @throws \Exception
     */
    protected function _doTransactionRefund()
    {
        $amount = (float)$this->_transaction->getRefundedAmount();
        if ($this->_order->hasCreditmemos()) {
            /**
             * @var $creditmemo  \Magento\Sales\Model\Order\Creditmemo
             */

            if ($this->_transaction->getCurrency() != $this->_order->getBaseCurrencyCode()) {
                $remain_amount = round($this->_order->getGrandTotal() - $amount, 2);
            } else {
                $remain_amount = round($this->_order->getBaseGrandTotal() - $amount, 2);
            }

            $status = \HiPay\FullserviceMagento\Model\Config::STATUS_REFUNDED;
            if ($remain_amount > 0) {
                $status = \HiPay\FullserviceMagento\Model\Config::STATUS_PARTIALLY_REFUNDED;
            }

            /**
             * @var $creditmemo Mage_Sales_Model_Order_Creditmemo
             */
            foreach ($this->_order->getCreditmemosCollection() as $creditmemo) {
                if (
                    $creditmemo->getState() == \Magento\Sales\Model\Order\Creditmemo::STATE_OPEN
                    && $this->_transaction->getOperation()->getId() == $creditmemo->getTransactionId()
                ) {
                    $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED);

                    $message = __('Refund accepted by Hipay.');

                    $this->_order->addStatusToHistory($status, $message);

                    if ($creditmemo->getInvoice()) {
                        $this->_transactionDB->addObject($creditmemo->getInvoice());
                    }

                    $this->_transactionDB->addObject($creditmemo)
                        ->addObject($this->_order);

                    $this->_transactionDB->save();

                    break;
                }
            }
        } elseif ($this->_order->canCreditmemo()) {
            $isCompleteRefund = true;
            $parentTransactionId = $this->_order->getPayment()->getLastTransId();

            $payment = $this->_order->getPayment()
                ->setPreparedMessage($this->_generateComment(''))
                ->setTransactionId($this->generateTransactionId("refund"))
                ->setCcTransId($this->_transaction->getTransactionReference())
                ->setParentTransactionId($parentTransactionId)
                ->setIsTransactionClosed($isCompleteRefund)
                ->registerRefundNotification($amount);

            $orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_REFUNDED;

            if ($this->_transaction->getStatus() == TransactionStatus::PARTIALLY_REFUNDED) {
                $orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_PARTIALLY_REFUNDED;
            }

            $this->_order->setStatus($orderStatus);

            $this->_order->save();

            try {
                $order = $this->orderRepository->get($this->_order->getId());
                $this->updateCouponUsages->execute($order, false);
            } catch (\Exception $e) {

            }

            $creditmemo = $payment->getCreatedCreditmemo();
            if ($creditmemo) {
                $this->creditmemoSender->send($creditmemo);
                $this->_order->addStatusHistoryComment(
                    __('You notified customer about creditmemo #%1.', $creditmemo->getIncrementId())
                )
                    ->setIsCustomerNotified(true);
                $this->_order->save();
            }
        }
    }

    /**
     * Process authorized and pending payment notification
     *
     * @return void
     */
    protected function _doTransactionAuthorizedAndPending()
    {
        $this->_order->getPayment()->setIsTransactionPending(true);

        $this->_order->getPayment()->setPreparedMessage($this->_generateComment(''))
            ->setTransactionId($this->_transaction->getTransactionReference() . "-auth-pending")
            ->setCcTransId($this->_transaction->getTransactionReference())
            ->setCurrencyCode($this->_transaction->getCurrency())
            ->setIsTransactionClosed(0)
            ->registerAuthorizationNotification((float)$this->_transaction->getAuthorizedAmount());

        $this->_order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
        );
        $this->_doTransactionMessage("Transaction is fraud challenged. Waiting for accept or deny action.");
        $this->_order->save();
    }

    /**
     * Process capture requested payment notification
     *
     * @return void
     */
    protected function _doTransactionCaptureRequested()
    {
        $this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
    }

    /**
     * Process refund requested payment notification
     *
     * @return void
     */
    protected function _doTransactionRefundRequested()
    {
        $this->_changeStatus(Config::STATUS_REFUND_REQUESTED, 'Refund Requested.');
    }

    /**
     * Process refund refused payment notification
     *
     * @throws \Exception
     */
    protected function _doTransactionRefundRefused()
    {
        $this->_changeStatus(Config::STATUS_REFUND_REFUSED, 'Refund Refused.');

        if ($this->_order->hasCreditmemos()) {
            foreach ($this->_order->getCreditmemosCollection() as $creditmemo) {
                if (
                    $creditmemo->getState() == \Magento\Sales\Model\Order\Creditmemo::STATE_OPEN
                    && $this->_transaction->getOperation()->getId() == $creditmemo->getTransactionId()
                ) {
                    $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_CANCELED);
                    $this->resetOrderRefund($creditmemo);
                    $this->resetInvoiceRefund($creditmemo);

                    if ($creditmemo->getInvoice()) {
                        $this->_transactionDB->addObject($creditmemo->getInvoice());
                    }

                    $this->_transactionDB->addObject($creditmemo)
                        ->addObject($this->_order);

                    $this->_transactionDB->save();

                    break;
                }
            }
        }
    }

    /**
     * Process capture refused payment notification
     *
     * @throws \Exception
     */
    protected function _doTransactionCaptureRefused()
    {
        $this->_changeStatus(Config::STATUS_CAPTURE_REFUSED, 'Capture Refused.');

        if ($this->_order->hasInvoices()) {
            foreach ($this->_order->getInvoiceCollection() as $invoice) {
                if (
                    $invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_OPEN
                    && $this->_transaction->getOperation()->getId() == $invoice->getTransactionId()
                ) {
                    $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED);

                    $this->_transactionDB->addObject($invoice)
                        ->addObject($this->_order);

                    $this->_transactionDB->save();

                    break;
                }
            }
        }
    }

    /**
     * Process denied payment notification
     *
     * @throws \Exception
     */
    protected function _doTransactionDenied()
    {
        $this->_order->getPayment()
            ->setTransactionId($this->_transaction->getTransactionReference() . "-denied")
            ->setCcTransId($this->_transaction->getTransactionReference())
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true)
            ->deny(false);

        $orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_refused');
        $this->_order->setStatus($orderStatus);

        $this->_order->save();
    }

    /**
     * Treat failed payment as order cancellation
     *
     * @return void
     */
    protected function _doTransactionFailure()
    {
        $this->_order->registerCancellation($this->_generateComment(''));
        $orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData('order_status_payment_refused');
        if (
            in_array(
                $this->_transaction->getStatus(),
                array(TransactionStatus::CANCELLED, TransactionStatus::EXPIRED)
            )
        ) {
            $orderStatus = $this->_order->getPayment()->getMethodInstance()->getConfigData(
                'order_status_payment_canceled'
            );
        }
        $this->_order->setStatus($orderStatus);
        $this->_order->save();
    }

    /**
     * Register authorized payment
     *
     * @return void
     */
    protected function _doTransactionAuthorization()
    {
        /**
         * @var $payment \Magento\Sales\Model\Order\Payment
        */
        $payment = $this->_order->getPayment();
        $payment->setTransactionAdditionalInfo('transac_currency', $this->_transaction->getCurrency());
        $payment->setTransactionAdditionalInfo('authorization_code', $this->_transaction->getAuthorizationCode());
        $payment->setPreparedMessage($this->_generateComment(''))
            ->setTransactionId($this->_transaction->getTransactionReference() . "-auth")
            ->setCcTransId($this->_transaction->getTransactionReference())
            ->setCurrencyCode($this->_transaction->getCurrency())
            ->setIsTransactionClosed(0)
            ->registerAuthorizationNotification((float)$this->_transaction->getAuthorizedAmount());

        if (($this->isFirstSplitPayment || $this->isSplitPayment) && $payment->getIsFraudDetected()) {
            $payment->setIsFraudDetected(false);
        }

        if (!$this->_order->getEmailSent()) {
            $this->orderSender->send($this->_order);
        }

        //Change last status history
        $histories = $this->_order->getStatusHistories();
        if (!empty($histories)) {
            $history = $histories[count($histories) - 1];
            $history->setStatus(Config::STATUS_AUTHORIZED);

            //Override message history
            $formattedAmount = $this->_order->getBaseCurrency()->formatTxt($this->_transaction->getAuthorizedAmount());
            $comment = __('Authorized amount of %1 online', $formattedAmount);
            $comment = $payment->prependMessage($comment);
            $comment .= __(' Transaction ID: %1', $this->_transaction->getTransactionReference() . '-auth');
            $history->setComment($comment);
        }

        //Set custom order status
        $this->_order->setStatus(Config::STATUS_AUTHORIZED);
        $this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);

        $this->_order->save();
    }

    /**
     * Process completed payment (either full or partial)
     *
     * @param  bool $skipFraudDetection
     * @return void
     */
    protected function _doTransactionCapture($skipFraudDetection = false)
    {
        /**
         * @var \Magento\Sales\Model\Order\Payment $payment
        */
        $payment = $this->_order->getPayment();
        $payment->setTransactionAdditionalInfo('transac_currency', $this->_transaction->getCurrency());
        $parentTransactionId = $payment->getLastTransId();

        $payment->setTransactionId($this->generateTransactionId("capture"));
        $payment->setCcTransId($this->_transaction->getTransactionReference());
        $payment->setCurrencyCode($this->_transaction->getCurrency());
        $payment->setPreparedMessage($this->_generateComment(''));
        $payment->setParentTransactionId($parentTransactionId);
        $payment->setShouldCloseParentTransaction(true);
        $payment->setIsTransactionClosed(0);

        $orderStatus = $payment->getMethodInstance()->getConfigData('order_status_payment_accepted');

        if ($this->_transaction->getStatus() == TransactionStatus::PARTIALLY_CAPTURED) {
            $orderStatus = \HiPay\FullserviceMagento\Model\Config::STATUS_PARTIALLY_CAPTURED;
        }

        $this->_order->setStatus($orderStatus);
        $this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);

        $payment->registerCaptureNotification(
            $this->_transaction->getCapturedAmount(),
            $skipFraudDetection
        );

        // notify customer
        $invoice = $payment->getCreatedInvoice();
        $invoiceFromDB = null;

        if (!$invoice) {
            $invoiceFromDB = $this->getInvoiceForTransactionId($this->_order, $payment->getTransactionId());
        }

        if (!$invoice && !$invoiceFromDB) {
            $invoice = $this->_order->prepareInvoice()->register();
            $invoice->setOrder($this->_order);
            $this->_order->addRelatedObject($invoice);
            $payment->setCreatedInvoice($invoice);
            $payment->setShouldCloseParentTransaction(true);
            $payment->setIsFraudDetected(false);
            if (!$invoiceFromDB) {
                $payment->registerCaptureNotification(
                    $this->_transaction->getCapturedAmount(),
                    $skipFraudDetection
                );
            }
        }

        $this->_order->save();

        if ($invoice && !$this->_order->getEmailSent()) {
            $this->orderSender->send($this->_order);
            $this->_order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(true);
            $this->_order->save();
        }
    }

    /**
     * Process voided authorization
     *
     * @return void
     */
    protected function _doTransactionVoid()
    {
        /**
         * @var $payment \Magento\Sales\Model\Order\Payment
        */
        $payment = $this->_order->getPayment();
        $parentTransactionId = $payment->getLastTransId();

        $this->_order->getPayment()
            ->setPreparedMessage($this->_generateComment(''))
            ->setParentTransactionId($parentTransactionId)
            ->registerVoidNotification();

        $this->_order->save();
    }

    /**
     * Generate an "Notification" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param  string $comment
     * @param  bool   $addToHistory
     * @return string|\Magento\Sales\Model\Order\Status\History
     */
    protected function _generateComment($comment = '', $addToHistory = false)
    {
        $message = __('Notification "%1"', $this->_transaction->getState());
        if ($comment) {
            $message .= ' ' . $comment;
        }

        if ($addToHistory) {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }

        return $message;
    }

    /**
     * Reset order data for refund
     * Creditmemo is in pending and wait for notification
     * So, we reset all totals refunded
     *
     * @param  \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function resetOrderRefund(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $this->_order;
        $baseOrderRefund = $this->priceCurrency->round(
            $order->getBaseTotalRefunded() - $creditmemo->getBaseGrandTotal()
        );
        $orderRefund = $this->priceCurrency->round(
            $order->getTotalRefunded() - $creditmemo->getGrandTotal()
        );
        $order->setBaseTotalRefunded($baseOrderRefund);
        $order->setTotalRefunded($orderRefund);

        $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded() - $creditmemo->getBaseSubtotal());
        $order->setSubtotalRefunded($order->getSubtotalRefunded() - $creditmemo->getSubtotal());

        $order->setBaseTaxRefunded($order->getBaseTaxRefunded() - $creditmemo->getBaseTaxAmount());
        $order->setTaxRefunded($order->getTaxRefunded() - $creditmemo->getTaxAmount());
        $order->setBaseDiscountTaxCompensationRefunded(
            $order->getBaseDiscountTaxCompensationRefunded() - $creditmemo->getBaseDiscountTaxCompensationAmount()
        );
        $order->setDiscountTaxCompensationRefunded(
            $order->getDiscountTaxCompensationRefunded() - $creditmemo->getDiscountTaxCompensationAmount()
        );

        $order->setBaseShippingRefunded($order->getBaseShippingRefunded() - $creditmemo->getBaseShippingAmount());
        $order->setShippingRefunded($order->getShippingRefunded() - $creditmemo->getShippingAmount());

        $order->setBaseShippingTaxRefunded(
            $order->getBaseShippingTaxRefunded() - $creditmemo->getBaseShippingTaxAmount()
        );
        $order->setShippingTaxRefunded($order->getShippingTaxRefunded() - $creditmemo->getShippingTaxAmount());

        $order->setAdjustmentPositive($order->getAdjustmentPositive() - $creditmemo->getAdjustmentPositive());
        $order->setBaseAdjustmentPositive(
            $order->getBaseAdjustmentPositive() - $creditmemo->getBaseAdjustmentPositive()
        );

        $order->setAdjustmentNegative($order->getAdjustmentNegative() - $creditmemo->getAdjustmentNegative());
        $order->setBaseAdjustmentNegative(
            $order->getBaseAdjustmentNegative() - $creditmemo->getBaseAdjustmentNegative()
        );

        $order->setDiscountRefunded($order->getDiscountRefunded() - $creditmemo->getDiscountAmount());
        $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded() - $creditmemo->getBaseDiscountAmount());

        $order->getPayment()->setAmountRefunded(
            $order->getPayment()->getAmountRefunded() - $creditmemo->getGrandTotal()
        );
        $order->getPayment()->setBaseAmountRefunded(
            $order->getPayment()->getBaseAmountRefunded() - $creditmemo->getBaseGrandTotal()
        );
        $order->getPayment()->setBaseAmountRefundedOnline(
            $order->getPayment()->getBaseAmountRefundedOnline() - $creditmemo->getBaseGrandTotal()
        );

        if ($creditmemo->getDoTransaction()) {
            $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOnlineRefunded($order->getBaseTotalOnlineRefunded() - $creditmemo->getBaseGrandTotal());
        } else {
            $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOfflineRefunded(
                $order->getBaseTotalOfflineRefunded() - $creditmemo->getBaseGrandTotal()
            );
        }

        $order->setBaseTotalInvoicedCost(
            $order->getBaseTotalInvoicedCost() + $creditmemo->getBaseCost()
        );
    }

    /**
     * Reset invoice data for refund
     *
     * @param  \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function resetInvoiceRefund(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        if ($creditmemo->getInvoice()) {
            $creditmemo->getInvoice()->setBaseTotalRefunded(
                $creditmemo->getInvoice()->getBaseTotalRefunded() - $creditmemo->getBaseGrandTotal()
            );
        }
    }

    /**
     *  Generate transaction ID for partial capture/refund
     *
     * @param  string $type
     * @return string Id transaction
     */
    protected function generateTransactionId($type)
    {
        if ($this->_transaction->getOperation()) {
            return $this->_transaction->getOperation()->getId();
        } else {
            return $this->_transaction->getTransactionReference() . "-" . $type;
        }
    }

    /**
     * Return invoice model for transaction
     *
     * @param  OrderInterface $order
     * @param  string         $transactionId
     * @return false|Invoice
     */
    protected function getInvoiceForTransactionId(OrderInterface $order, $transactionId)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                return $invoice;
            }
        }
        foreach ($order->getInvoiceCollection() as $invoice) {
            if (
                $invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_OPEN
                && $invoice->load($invoice->getId())
            ) {
                $invoice->setTransactionId($transactionId);
                return $invoice;
            }
        }
        return false;
    }
}
