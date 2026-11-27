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

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order;

/**
 * Signed token of the HiPay cancel redirect URL
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class RedirectToken
{
    public const PARAM = 'token';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Build the token added to the cancel URL
     *
     * @param  Order $order
     * @return string
     */
    public function generate(Order $order)
    {
        return $this->encryptor->hash('hipay_redirect_' . $order->getIncrementId());
    }

    /**
     * Check the token received on the cancel redirect
     *
     * @param  Order $order
     * @param  mixed $token
     * @return bool
     */
    public function isValid(Order $order, $token)
    {
        return is_string($token) && $token !== '' && hash_equals($this->generate($order), $token);
    }
}
