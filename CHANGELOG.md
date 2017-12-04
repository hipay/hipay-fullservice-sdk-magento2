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