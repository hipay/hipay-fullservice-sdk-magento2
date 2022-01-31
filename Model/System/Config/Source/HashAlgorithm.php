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

namespace HiPay\FullserviceMagento\Model\System\Config\Source;

use HiPay\Fullservice\Enum\Helper\HashAlgorithm as HashAlgorithmSDK;

/**
 * Source model for available payment actions
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HashAlgorithm implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getHashAlgorithms();
    }

    /**
     * Hash algorithms getter
     *
     * @return array
     */
    public function getHashAlgorithms()
    {
        $hashAlgorithms = [
            HashAlgorithmSDK::SHA1 => __('sha1'),
            HashAlgorithmSDK::SHA256 => __('sha256'),
            HashAlgorithmSDK::SHA512 => __('sha512'),
        ];

        return $hashAlgorithms;
    }
}
