# HiPay Fullservice module for Magento 2

[![Build Status](https://hook.hipay.org/badge-ci/build/pi-ecommerce/hipay-fullservice-sdk-magento2/develop?service=github)](https://hook.hipay.org/badge-ci/build/pi-ecommerce/hipay-fullservice-sdk-magento2/develop?service=github)
[![GitHub license](https://img.shields.io/badge/license-Apache%202-blue.svg)](https://raw.githubusercontent.com/hipay/hipay-fullservice-sdk-magento2/master/LICENSE.md)

The **HiPay Fullservice module for Magento 2** is a PHP module which allows you to accept payments in your Magento 2 online store. It offers innovative features to reduce shopping cart abandonment rates, optimize success rates and enhance the purchasing process on merchants’ sites in order to significantly increase business volumes without additional investments in the Magento 2 e-commerce CMS solution.

## Getting started

Read the **[project documentation][doc-home]** for comprehensive information about the requirements, general workflow and installation procedure.

## Resources

- [Full project documentation][doc-home] — To have a comprehensive understanding of the workflow and get the installation procedure
- [HiPay Support Center][hipay-help] — To get technical help from HiPay
- [Issues][project-issues] — To report issues, submit pull requests and get involved (see [Apache 2.0 License][project-license])
- [Change log][project-changelog] — To check the changes of the latest versions
- [Contributing guidelines][project-contributing] — To contribute to our source code

## Features

- Compatibility with Magento 2
- 3-D Secure enabling/disabling
- Oneclick option configuration with custom rules
- Management of multiple cards per customer for one-click payment
- iFrame integration, hosted page and custom card API
- Mail management for transactions pending fraud validation (challenged)
- Manual and automatic capture
- Partial capture and refund
- Payment in x installments without fees
- Subscription management (development in progress)

## Support

HiPay frequently releases new versions of the modules. It is imperative to regularly update your platforms to be compatible with the versions of HiPay’s APIs, which are also evolving.
HiPay offers support services provided that your platforms run on maintained PHP versions and updated CMS versions with the latest security patches (see the list below).
We are obligated to follow each publisher’s minimum recommendations.
If you encounter an issue while using the modules, before contacting our Support team, we invite you to:

- analyze your platform’s PHP logs as well as the logs specific to the HiPay module,
- update the module to the most recent version,
- perform similar tests on your stage environments,
- analyze possible overloading in the code and interferences with third-party modules,
- perform tests on a blank environment without your developments or any third-party modules.

## Requirements

If you encounter problems when saving the configuration, you must increase the value of the php property "**max_input_vars**"

Minimum value:  

`max_input_vars = 10000`

## License

The **HiPay Fullservice module for Magento 2** is available under the **Apache 2.0 License**. Check out the [license file][project-license] for more information.

[doc-home]: https://developer.hipay.com/cms-modules/magento/magento-2-enterprise
[hipay-help]: http://help.hipay.com
[project-issues]: https://github.com/hipay/hipay-fullservice-sdk-magento2/issues
[project-license]: LICENCE.md
[project-changelog]: CHANGELOG.md
[project-contributing]: CONTRIBUTING.md
