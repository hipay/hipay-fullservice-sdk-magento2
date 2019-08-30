 # 1.7.0
  - Init 3DS V2

 # 1.6.1
  - Update payment method configuration form
  - Fix issue [#119](https://github.com/hipay/hipay-fullservice-sdk-magento2/issues/119)
  
 # 1.6.0
  - Add MyBank payment method
  
 # 1.5.0
  - Add error message on field
  - Update payment method configuration
  
 # 1.4.3
  - Fix bad require and bug with magento validation
  - Improve notification capture with authorization

# 1.4.2
  - Fix multi_use for one-click
  - Fix Magento 2.3 compliance
  
# 1.4.1
  - Fix entity name for new magento checks
  - Fix Magento 2.3 compliance
  
 # 1.4.0
 - **Add Payment Method Hosted Fields** 
 
 # 1.3.4
 - Fix cvv not mandatory for Maestro card
 - Add command to convert data serialized to json 
 
If you are migrating Magento from 2.1.X to 2.2.X and have defined 3DS or OneClick rules, you will need to run the new command to perform the migration.
See: https://devdocs.magento.com/guides/v2.2/release-notes/backward-incompatible-changes/

After your magento upgrade, execute in your magento directory:
  
     php bin/magento hipay:upgradeToJson
 

# 1.3.3
- Fix the exception with manual captures
- Fix compilation error

# 1.3.2

- [#114](https://github.com/hipay/hipay-fullservice-sdk-magento2/issues/114) Fix issue #114 : Fatal error on failure order (#114)
- Fix : Split payment AI method cannot be displayed alone

# 1.3.1

- Fix : Change refund workflow
- Fix : Split payment status

# 1.3.0

- Add proxy settings (gateway requests)
- Fix : SEPA remove electronic signature
- Fix : partial refund

# 1.2.2

- Fix : Astropay payment method bug
- Fix : Category mapping bug
- Code rework/reformatting

# 1.2.1

- Fix : Api call wrongly formatted (gender)
- Fix : Issue [#107](https://github.com/hipay/hipay-fullservice-sdk-magento2/issues/107)
- Fix : Error on DI compilation [#113](https://github.com/hipay/hipay-fullservice-sdk-magento2/pull/113)
- Add casperJS tests
- Improve CI

# 1.2.0

- Add support for multi-currency payments
- Add hash algorithm selection in BO
- Add notification URL support
- Fix notification response (500 HTTP code on fail)
- Fix : split payment
- Fix : MOTO
- Fix : basket

# 1.1.8

- Add support for product without category and option basket

# 1.1.7

- New payment method support : Bnp Personal Finance 3X and 4X 

# 1.1.6

- Fix invoice/credit-memo status (Pending/Paid) for partials captures

# 1.1.5

- Add time_limit_to_pay in HostedPaymentPageRequest
- Remove CDATA parameters ( Use custom_data now )

# 1.1.4

Fixed bugs:
  - Missing authentication indicator

# 1.1.3

Fixed bugs:
  - Error with RequestHandler compilation

# 1.1.2

Fixed bugs:
 - Fix autoclosing tag for fingerprint javascript inclusion
 - Change composer installation. Remove module installation in folder (app/code/Hipay). Now installation is in folder vendor
 - Fix wrong invoice status with multiple partials capture
 - Bad configuration for fingerprint.js

New feature:
 - Parameter support  : "operation_id"

# 1.1.1

- Bugfix CCType for hosted payment

# 1.1.0

- New feature FACILY PAY ONEY
- New feature KLARNA
- New feature Adding request sources
- New feature Adding custom data
- New feature Adding device fingerprint
- New feature ASTROPAY
- New feature SEPA SDD
- New feature POSTFINANCE
- New feature SOFORT
- New feature WEBMONEY & YANDEX
- New feature PRZELEWY24
- New feature GIROPAY
- New feature iDEAL
- New feature PAYPAL
- New feature Basket V2 (cart synced to HiPay)
- New feature Mapping categories Website <> HiPay
- New feature Mapping carriers Website <> HiPay with delivery date synced to HiPay
- FIX New branding
- FIX translations

# 1.0.8

- Skip Magento fraud checking

# 1.0.7

- Bugfix Add display selector value to TPP request

# 1.0.6

- Bugfix template iFrame send by the request new order

# 1.0.5

- Update documentation URL to the HiPay portal developer

# 1.0.4

- Bugfix PeriodUnit method and Docker image

# 1.0.3

- Bugfix namespace in class Sisal, Qiwi Wallet and unit tests

# 1.0.2

- Update version composer.json with new version PHP SDK and bumps package version

# 1.0.1

- README updates

