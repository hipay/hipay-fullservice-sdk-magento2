require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    var isReloaded = false;

    $(document).on('ajaxComplete', function(){
        if (!isReloaded) {
            var sections = ['cart'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);

            isReloaded = true;
        }
    });
});
