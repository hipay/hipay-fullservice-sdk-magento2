<?php
/**
 * HiPay fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Controller\Payment;

use HiPay\FullserviceMagento\Controller\Fullservice;

/**
 * Refresh checkout configuration controller
 *
 * Ajax call used to update split payment array in front
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class RefreshCheckoutConfig extends \HiPay\FullserviceMagento\Controller\Fullservice
{

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Method\SplitConfigProvider $splitConfigProvider
     */
    protected $splitConfigProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $hipaySession,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory,
        \HiPay\FullserviceMagento\Model\SecureVault\Factory $vaultManagerFactory,
        \HiPay\FullserviceMagento\Model\Method\SplitConfigProvider $splitConfigProvider
    ) {

        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $hipaySession,
            $logger,
            $gatewayManagerFactory,
            $vaultManagerFactory
        );

        $this->splitConfigProvider = $splitConfigProvider;
    }

    /**
     * Refresh a part of chechoutConfig
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->getResponse()->representJson(\Zend_Json::encode($this->splitConfigProvider->getConfig()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getResponse()->representJson(json_encode(array(
                "code" => $e->getCode(),
                "message" => $e->getMessage()
            )));
            $this->getResponse()->setStatusHeader(400, '1.1');
        } catch (\Exception $e) {
            $this->getResponse()->representJson(json_encode(array(
                "code" => $e->getCode(),
                "message" => __('We can\'t refresh checkout config.')
            )));
            $this->getResponse()->setStatusHeader(400, '1.1');
            $this->logger->addDebug($e->getMessage());
        }
    }
}
