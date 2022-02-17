casper.test.begin('Test Notification on Magento2', function (test) {
  phantom.clearCookies();

  casper
    .start(baseURL)
    .then(function () {
      if (this.visible('p[class="bugs"]')) {
        test.done();
      }
    })
    .thenOpen(baseURL + 'admin/', function () {
      this.logToBackend();
    })
    .then(function () {
      test.assertExists(
        'div.message-system-collapsible',
        'Notification block is found'
      );
      this.click(
        'li.level0:nth-child(9) > ul:nth-child(2) > li:nth-child(2) > a:nth-child(1)'
      );
    })
    .then(function () {
      test.assertExists(
        'a[href^="https://github.com/hipay/hipay-fullservice-sdk-magento1/releases/tag/"]',
        'Full notification is found'
      );
    })
    .thenOpen(baseURL + 'admin/admin/notification/index/', function () {
      test.assertMatch(
        this.fetchText('td.col-title'),
        /HiPay enterprise .* available!/,
        'Notification message is OK'
      );
    })
    .run(function () {
      test.done();
    });
});
