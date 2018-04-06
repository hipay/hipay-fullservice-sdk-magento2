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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Model\Request\SecureVault;

use HiPay\FullserviceMagento\Model\Request\AbstractRequest as BaseRequest;
use HiPay\Fullservice\SecureVault\Request\GenerateTokenRequest;


/**
 * Generate Token SecureVault Request Object
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class GenerateToken extends BaseRequest
{

    /**
     *
     * @var \Magento\Sales\Model\Order\Payment $_payment
     */
    protected $_payment;

    /**
     * {@inheritDoc}
     * @see \HiPay\FullserviceMagento\Model\Request\AbstractRequest::__construct()
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HiPay\FullserviceMagento\Model\Request\Type\Factory $requestFactory,
        \Magento\Framework\Url $urlBuilder,
        \HiPay\FullserviceMagento\Helper\Data $helper,
        $params = []
    ) {

        parent::__construct($logger, $checkoutData, $customerSession, $checkoutSession, $localeResolver,
            $requestFactory, $urlBuilder, $helper, $params);


        if (isset($params['payment']) && $params['payment'] instanceof \Magento\Sales\Model\Order\Payment) {
            $this->_payment = $params['payment'];
        } else {
            throw new \Exception('Payment instance is required.');
        }

    }


    /**
     * @return \HiPay\Fullservice\SecureVault\Request\GenerateTokenRequest
     */
    protected function mapRequest()
    {


        $generateRequest = new GenerateTokenRequest();
        $generateRequest->card_number = $this->_payment;

        return $generateRequest;
    }

}
