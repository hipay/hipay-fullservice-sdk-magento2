<?php
/*
 * Hipay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Hipay_Fullservice',
	__DIR__
	//Test is link for vagrant sandbox
   /* is_link('/var/www/magento2/vendor/hipay/hipay-fullservice-sdk-magento2') ? '/var/www/magento2/vendor/hipay/hipay-fullservice-sdk-magento2' : __DIR__*/
);