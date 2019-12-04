# Mollie Payments Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

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
