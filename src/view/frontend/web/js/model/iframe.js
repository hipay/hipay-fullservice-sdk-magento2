/*
 * HiPay fullservice SDK
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
