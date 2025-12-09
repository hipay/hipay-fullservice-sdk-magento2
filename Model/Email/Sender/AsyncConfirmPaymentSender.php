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

use HiPay\FullserviceMagento\Model\Email\Container\AsyncConfirmPaymentIdentity;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * HiPay Asynchronous Confirm Payment Email Sender
 *
 * Sends an email with Multibanco or Mooney/SisalPay payment instructions
 * when HiPay notifies Magento (async payment methods).
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class AsyncConfirmPaymentSender extends Sender
{
    /** HiPay asynchronous methods */
    private const MULTIBANCO_METHODS = ['hipay_multibanco', 'hipay_multibanco_hosted_fields'];
    private const MOONEY_METHODS = ['hipay_sisal', 'hipay_sisal_hosted_fields'];

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    private $referenceToPay = [];

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Template                    $templateContainer
     * @param AsyncConfirmPaymentIdentity $identityContainer
     * @param SenderBuilderFactory        $senderBuilderFactory
     * @param LoggerInterface             $logger
     * @param Renderer                    $addressRenderer
     * @param AssetRepository             $assetRepo
     * @param TimezoneInterface           $timezone
     */
    public function __construct(
        Template $templateContainer,
        AsyncConfirmPaymentIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        AssetRepository $assetRepo,
        TimezoneInterface $timezone
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->assetRepo = $assetRepo;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * Send async payment instruction email
     *
     * @param Order             $order
     * @param string|array|null $referenceToPay
     * @return bool
     * @throws LocalizedException
     */
    public function send(Order $order, $referenceToPay): bool
    {
        $method = $order->getPayment()->getMethod();

        $referenceToPay = is_array($referenceToPay)
            ? $referenceToPay
            : (json_decode((string)$referenceToPay, true) ?: []);

        $this->referenceToPay = $this->prepareReferenceToPay($method, $referenceToPay);

        $this->prepareTemplate($order);

        return $this->checkAndSend($order);
    }

    /**
     * Prepare email template variables.
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order): void
    {
        $createdAtFormatted = $this->timezone->formatDateTime(
            $order->getCreatedAt(),
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            null,
            $order->getStore()->getLocaleCode()
        );

        $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'store' => $order->getStore(),
            'payment_method_title' => $order->getPayment()->getMethodInstance()->getTitle(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'referenceToPay' => $this->referenceToPay,
            'customer_name' => $order->getCustomerName(),
            'created_at_formatted' => $createdAtFormatted,
            'order_data' => [
                'is_not_virtual' => $order->getIsNotVirtual(),
                'increment_id' => $order->getIncrementId(),
            ],
        ];

        $this->templateContainer->setTemplateVars((new DataObject($transport))->getData());

        parent::prepareTemplate($order);
    }

    /**
     * Prepare reference data with logo/barcode depending on payment method
     *
     * @param string $method
     * @param array  $referenceToPay
     * @return array
     * @throws LocalizedException
     */
    protected function prepareReferenceToPay(string $method, array $referenceToPay): array
    {
        $referenceToPay['isMultibanco'] = in_array($method, self::MULTIBANCO_METHODS, true);
        $referenceToPay['isMooney'] = in_array($method, self::MOONEY_METHODS, true);

        if ($referenceToPay['isMultibanco']) {
            $referenceToPay = $this->prepareMultibancoData($referenceToPay);
        } elseif ($referenceToPay['isMooney']) {
            $referenceToPay = $this->prepareMooneyData($referenceToPay);
        }

        return $referenceToPay;
    }

    /**
     * Enrich Multibanco reference data
     *
     * @param array $referenceToPay
     * @return array
     * @throws LocalizedException
     */
    private function prepareMultibancoData(array $referenceToPay): array
    {
        $referenceToPay['logo'] = $this->getImageUrl('multibanco.png');
        $referenceToPay['barcode_image'] = '';
        return $referenceToPay;
    }

    /**
     * Enrich Mooney/Sisal reference data.
     *
     * @param array $referenceToPay
     * @return array
     * @throws LocalizedException
     */
    private function prepareMooneyData(array $referenceToPay): array
    {
        $referenceToPay['logo'] = $this->getImageUrl('mooney.png');

        if (!empty($referenceToPay['barCode'])) {
            try {
                $generator = new BarcodeGeneratorPNG();
                $barcodePng = $generator->getBarcode($referenceToPay['barCode'], $generator::TYPE_CODE_128, 4, 100);
                $referenceToPay['barcode_image'] = 'data:image/png;base64,' . base64_encode($barcodePng);
            } catch (\Exception $e) {
                $this->logger->error('[HiPay] Error generating barcode: ' . $e->getMessage());
                $referenceToPay['barcode_image'] = '';
            }
        }

        return $referenceToPay;
    }

    /**
     * Retrieve the public URL of an image
     *
     * @param string $filename
     * @return string
     * @throws LocalizedException
     */
    private function getImageUrl(string $filename): string
    {
        return $this->assetRepo
            ->createAsset('HiPay_FullserviceMagento::images/local/' . $filename, ['area' => 'frontend'])
            ->getUrl();
    }
}
