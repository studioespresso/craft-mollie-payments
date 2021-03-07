# Mollie Payments Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 2.0.0 - 2021-03-07
### Added
- The plugin now supports Craft's Project Config
- E-mail address has been added to the meta data sent to Mollie
- Requires Craft 3.5.0 or higher

## 1.6.4 - 2021-02-26
### Fixed
- Fixed an issue with querring payments by status ([#23](https://github.com/studioespresso/craft-mollie-payments/issues/23))

## 1.6.3 - 2021-02-15
### Fixed
- Fixed support for PostgreSQL (Thanks @boboldehampsink [#21](https://github.com/studioespresso/craft-mollie-payments/pull/21))


## 1.6.2 - 2020-12-19
### Fixed
- Fixed validation for custom fields


## 1.6.1 - 2020-10-13
### Fixed
- Proper validation for payment elements


## 1.6.0 - 2020-10-05
### Added
- The plugin now supports using a different API key per site. [#12](https://github.com/studioespresso/craft-mollie-payments/issues/12)

## 1.5.3 - 2020-10-05
### Added
- Added a statuc column to the all payments export

## 1.5.2 - 2020-04-15
### Fixed
- Fixed an issue with deleting payment forms

## 1.5.1 - 2020-04-01
### Fixed
- Fixed an issue the donate action when using a multi-step form 

## 1.5.0 - 2020-03-07
### Added
- Added a `donate` action so you can have forms where the user can choose the price. An example form can be found [here](https://studioespresso.github.io/craft-mollie-payments/template.html#donation-form) 

## 1.4.2 - 2020-03-04
### Fixed
- Fix an error where payment element wouldn't be assign an email address.

## 1.4.1 - 2020-03-02
### Fixed
- Payments with status "in cart" can now be viewed & clicked in the CP

## 1.4.0 - 2020-03-01
### Added
- Date created is now shown on element overviews ([#7](https://github.com/studioespresso/craft-mollie-payments/issues/7))
- Payments can now also be done in multi-step forms, using your own controller action. Docs can be found [here](https://studioespresso.github.io/craft-mollie-payments/template.html#multi-step-form)

## 1.3.0 - 2019-12-08
### Added
- Added ``craft.payments``, making it easy to display data from the payment element on confirmation & profile pages. ([#6](https://github.com/studioespresso/craft-mollie-payments/issues/6)) 

## 1.2.0 - 2019-12-04
### Added
- The payment description visible in Mollie can now be set per payment form and  can include values from the custom fields that are assigned ot the form. 

## 1.1.5 - 2019-11-30
### Fixed 
- Fixed a typo that caused the webhook post to crash

## 1.1.4 - 2019-11-28
### Fixed 
- Payment amounts can now be decimals and not just numbers

## 1.1.3 - 2019-11-27
### Fixed 
- Fixed an issue with conflicting variable names in the forms index template

## 1.1.2 - 2019-11-26
### Fixed 
- Fixed a missing variable when handling free payments

## 1.1.1 - 2019-11-25
### Fixed 
- Fixed the first attribute in the CP index view for pre Craft 3.2 installs

## 1.1.0 - 2019-11-25
### Added
- Payments for a zero amount can now be handled within the same flow as regular payments

### Fixed 
- Fixed `formId` attribute  in Payments query, fixing overview per forms in the CP

## 1.0.0 - 2019-11-24
### Added
- Added action to export selected payments to csv
- Added action to select all payments to csv

## 1.0.0-beta.2
### Added
- Payment elements can now be deleted from the overview
- Email is now the main UI label for each element in the overview
- `EVENT_BEFORE_PAYMENT_SAVE` event

### Fixed
- Payment records are now deleted when the element is deleted

## 1.0.0-beta.1
### Added
- Initial release
