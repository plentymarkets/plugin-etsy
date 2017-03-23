# Release Notes for Etsy

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