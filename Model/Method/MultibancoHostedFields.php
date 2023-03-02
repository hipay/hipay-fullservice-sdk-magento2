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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Model\Method;

use HiPay\Fullservice\Gateway\Mapper\TransactionMapper;
use HiPay\Fullservice\Enum\Transaction\TransactionState;

/**
 * Multibanco Hosted Fields Model payment method
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class MultibancoHostedFields extends LocalHostedFields
{
    public const HIPAY_METHOD_CODE = 'hipay_multibanco_hosted_fields';

    /**
     * @var string
     */
    protected $_infoBlockType = 'HiPay\FullserviceMagento\Block\Hosted\Multibanco\Info';

    /**
     * @var string
     */
    protected static $_technicalCode = 'multibanco';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;

    /**
     * @var string[] keys to import in payment additionnal informations
     */
    protected $_additionalInformationKeys = ['browser_info', 'cc_type'];

    /**
     * Set pending state if transaction state if forwarding & get pending url
     * @param \HiPay\Fullservice\Gateway\Model\Transaction $response
     * @return string Redirect URL
     * @throws LocalizedException
     */
    protected function processResponse($response)
    {
        if ($response->getState() === TransactionState::FORWARDING) {
            $transaction = $response->toArray();
            $transaction['state'] = TransactionState::PENDING;
            $response = (new TransactionMapper($transaction))->getModelObjectMapped();
        }
        
        return parent::processResponse($response);
    }

    /**
     * Place order & set reference to pay information
     * @param \Magento\Payment\Model\InfoInterface $payment
     */
    public function place(\Magento\Payment\Model\InfoInterface $payment)
    {
        parent::place($payment);

        $payment->setAdditionalInformation(
            'reference_to_pay',
            $payment->getAdditionalInformation('response')['reference_to_pay']
        );

        return $this;
    }
}
