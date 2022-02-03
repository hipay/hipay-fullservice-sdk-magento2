require(
    [
    'jquery',
    'Magento_Customer/js/customer-data'
    ],
    function ($, customerData) {
        $(document).on(
            'ajaxComplete',
            function () {
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
            }
        );
    }
);
