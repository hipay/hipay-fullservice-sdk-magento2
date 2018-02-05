define(function () {
    'use strict';

    var mixin = {
        isBaseGrandTotalDisplayNeeded: function () {
            var totals = this.totals();
            if (!totals) {
                return false;
            }

            if(window.checkoutConfig.payment.hiPayFullservice.useOrcerCurrency){
                return false;
            }

            return totals.base_currency_code != totals.quote_currency_code;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
