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

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'HiPay_FullserviceMagento',
	__DIR__
	//Test is link for vagrant sandbox
   /* is_link('/var/www/magento2/vendor/hipay/hipay-fullservice-sdk-magento2') ? '/var/www/magento2/vendor/hipay/hipay-fullservice-sdk-magento2' : __DIR__*/
);
