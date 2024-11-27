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

namespace HiPay\FullserviceMagento\Controller\Card;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use HiPay\FullserviceMagento\Model\CardFactory;
use HiPay\FullserviceMagento\Model\Card;

/**
 * Register new card
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CardFactory
     */
    private $cardFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Session $customerSession
     * @param CardFactory $cardFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $customerSession,
        CardFactory $cardFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->cardFactory = $cardFactory;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('Customer not logged in')
            ])->setHttpResponseCode(403);
        }

        try {
            $cardData = json_decode($this->getRequest()->getContent(), true);

            // Create new card model
            $card = $this->cardFactory->create();

            $cardName = sprintf(
                '%s •••• %s - Expires %s/%s',
                ucfirst(strtolower($cardData['brand'])),
                substr($cardData['pan'], -4),
                $cardData['card_expiry_month'],
                $cardData['card_expiry_year']
            );
            // Set card data
            $card->setCustomerId($this->customerSession->getCustomerId())
                ->setName($cardName)
                ->setCcToken($cardData['token'])
                ->setCcType(strtolower($cardData['brand']))
                ->setCcExpMonth($cardData['card_expiry_month'])
                ->setCcExpYear($cardData['card_expiry_year'])
                ->setCcOwner($cardData['card_holder'])
                ->setCcNumberEnc($cardData['pan'])
                ->setCclast4(substr($cardData['pan'], -4))
                ->setCcStatus(Card::STATUS_ENABLED)
                ->setIsDefault(0)
                ->setCreatedAt(new \DateTime());

            // Save card
            $card->save();

            return $result->setData([
                'success' => true,
                'card_id' => $card->getId()
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ])->setHttpResponseCode(400);
        }
    }
}