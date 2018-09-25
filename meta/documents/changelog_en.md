# Release Notes für Etsy

## v1.3.0 (2018-09-26)
### Added
- The legal information will be added to the item description.

## v1.2.24 (2018-09-11)
### Added
- Added a log.

## v1.2.23 (2018-08-28)
### Changed
- Tags can now contain spaces.

## v1.2.22 (2018-08-27)
### Added
- Added instructions for error logs.

## v1.2.21 (2018-07-17)
### Changed
- Changed logs for some services.

## v1.2.20 (2018-07-13)
### Fixed
- An error was fixed which prevented items from being updated.

## v1.2.19 (2018-07-09)
### Changed
- The information regarding the installation of the plugin was adjusted in the user guide.

## v1.2.18 (2018-07-09)
### Fixed
- An issue was fixed which caused that the plugin couldn't be build.

## v1.2.17 (2018-06-05)
### Changed
- Changed the log level for several logs.

## v1.2.16 (2018-05-09)
### Fixed
- The plugin config is multilingual.

## v1.2.15 (2018-05-08)
### Fixed
- An issue was fixed which caused that the billing address could not be created.

## v1.2.14 (2018-05-02)
### Fixed
- The indicated shipping address is also used as invoice address.

## v1.2.13 (2018-04-26)
### Added
- Information about required rights for variable user classes was added to the user guide.

## v1.2.12 (2018-04-23)
### Fixed
- Properties without groups are shown correctly.

## v1.2.11 (2018-02-20)
### Fixed
- An issue was fixed which caused that the plentymarkets categories were shown multiple times.

## v1.2.10 (2018-02-20)
### Changed
- Updated plugin short description.

## v1.2.9 (2018-01-23)
#### Fixed
- An issue was fixed which caused event procedures (shipping and payment confirmation) not to be carried out correctly. 

## v1.2.8 (2018-01-16)
#### Changed
- The structure of the external order id was changed so that PayPal payments have a higher matching rate.

## v1.2.7 (2018-01-15)
#### Fixed
- A bug was fixed that prevented the plugin from being successfully built.

## v1.2.6 (2018-01-05)
#### Added
- New logs for the etsy event actions.

## v1.2.5 (2017-12-29)
#### Changed
- More info is now shown if listing could not be started.

## v1.2.4 (2017-12-19)
#### Fixed
- A bug was fixed that prevented that properties were sometimes not shown correctly.

## v1.2.3 (2017-12-18)
#### Fixed
- A bug was fixed that prevented properties being displayed in the right language.

## v1.2.2 (2017-11-29)
#### Fixed
- A bug was fixed that prevented simultaneous editing of newly added category or property correlations.

## v1.2.1 (2017-11-22)
#### Added
- Possibility to import gift informations and gift message.
- Buyer and payment messages are now also imported.

#### Fixed
- Fixed item title display bug that occurred after importing orderd.

## v1.2.0 (2017-11-20)
#### Changed
- This updated brings UI changes on all plugin sections. The views fulfill the newest Terra style guide and makes the plugin more friendlier.

#### Added
- It is now possible to delete an account and all its settings at once.

## v1.1.13 (2017-11-06)
### Fixed
- Listing drafts that do not successfully start were sometimes not deleted

## v1.1.12 (2017-11-03)
### Fixed
- Added additional checks regarding the vat issue which prevented the order import.

## v1.1.11 (2017-11-02)
### Fixed
- Fixed an issue regarding the vat which prevented the order import.

## v1.1.10 (2017-10-10)
### Added
- From now on, Etsy coupons will be added as item positions to the order.

## v1.1.9 (2017-10-09)
### Added
- It is now possible to add the "What is it" property to items.

## v1.1.8 (2017-09-09)
### Fixed
- Fixed an issue regarding price formatting which prevented the item export.

## v1.1.7 (2017-07-24)
### Fixed
- Added more information to the log entries regarding translations upload

## v1.1.6 (2017-06-26)
### Fixed
- The dropdowns at the settings and the shipping profiles now preselect the correct default value.

## v1.1.5 (2017-06-19)
### Fixed
- The item export now considers the generic item images and the images linked with the variation.

## v1.1.4 (2017-06-14)
### Fixed
- Fixed an issue with unused SKUs that were not removed.

## v1.1.3 (2017-06-06)
### Fixed
- Receipts without ZIP code will now be imported.

## v1.1.2 (2017-06-06)
### Fixed
- Once a listing is deleted directly from Etsy, its SKU will be also deleted from plentymarkets

## v1.1.1 (2017-06-02)
### Changed
- Added some log messages for listing start/update

## v1.1.0 (2017-05-31)
### Changed
- Updated Etsy platform categories 

## v1.0.17 (2017-05-08)
### Fixed
- An error occured while creating listings with more than one attribute. This has been fixed.

## v1.0.16 (2017-05-04)
### Fixed
- Fixed an error regarding the chosen shop language.

## v1.0.15 (2017-05-04)
### Fixed
- Errors that occur during listing start are now also logged.

## v1.0.14 (2017-05-04)
### Fixed
- Fixed some issues regarding the item shipping profile export.

## v1.0.13 (2017-05-02)
### Fixed
- Fixed some issues regarding the item measurements export.

## v1.0.12 (2017-04-18)
### Fixed
- fixed some issues regarding tags and title export.

## v1.0.11 (2017-04-03)
### Changed
- the plugin now uses the latest Terra components version.
### Fixed
- category names are now displayed in the login language.

## v1.0.10 (2017-03-29)
### Changed
- The listing update process was changed. Listing translations will be updated firstly and other data afterwards.

## v1.0.9 (2017-03-24)
### Changed
- Tags with more than 20 letters will not be exported anymore

## v1.0.8 (2017-03-23)
### Changed
- Tags that are duplicate will not be exported anymore

## v1.0.7 (2017-03-21)
### Fixed
- Use the write capacity unit settings for DynamoDB throughput 
### Changed
- Only the first 13 item tags are exported, the rest is ignored
### Added
- Added some better logging functionality for article export and order import 

## v1.0.6 (2017-03-16)
### Fixed
- All 244 Etsy countries can now be matched with the plentymarkets list of countries 
- Etsy orders are imported since the last successful run if communication problems with Etsy occur

## v1.0.5 (2017-03-06)
### Fixed
- An error was fixed which caused that sometimes the event actions did not work
- Pictures are uploaded in the same order as provided in the item
- Fixed a bug that prevented the listing to be created if all shipping profiles were selected in the item

## v1.0.4 (2017-03-03)
### Fixed
- An error was fixed which caused that items were not correctly assigned during the item import.

## v1.0.3 (2017-02-28)
### Fixed
- An issue that caused that sometimes the external payment ID was not imported
- Additional address information is now also imported

## v1.0.2 (2017-02-24)
### Fixed
- An UI issue that caused that sometimes settings were not completely saved 

## v1.0.1 (2017-02-22)
### Changed
- Small UI changes.

## v1.0.0 (2017-02-20)
### Added
- Added initial plugin files
