# Mollie Payments Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.1.2 - 2019-11-27
### Fixed 
- Fixed an issue with conflicting variable names in the forms index template


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
