/*
 * HiPay fullservice SDK
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
define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';

        var isInAction = ko.observable(false);

        return {
            isInAction: isInAction,
            stopEventPropagation: function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            }
        };
    }
);
