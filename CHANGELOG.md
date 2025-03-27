# Changelog

![keep a changelog](https://img.shields.io/badge/Keep%20a%20Changelog-v1.1.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog] for information about writing changes to this log.

## [Unreleased]

## [1.1.6] - 2025-03-27

- Fix special char handling in event excerpt field
- Fix start/end not required for occurrences in EasyAdmin
- Set "updated at" to newest of either entity or entity relations when indexing

## [1.1.5] - 2025-03-12

- Add labels to Woodpecker workflow
- Add stg Woodpecker workflow
- Add missing license

## [1.1.4] - 2025-03-05

- Include path in check for local image resource to avoid falsely identifying images from "v1" as local

## [1.1.3] - 2025-03-03

- Change DailyOccurrences to split midnight local time

## [1.1.2] - 2025-02-13

- Add users requested organizations to user view for admins

## [1.1.1] - 2025-02-13

- Only super admin can edit/create/delete feeds

## [1.1.0] - 2025-02-13

- Update dependencies
- Enable EasyAdmin pretty URLs
- Switch from Psalm to PHPStan
- Upgrade to PHP 8.4
- Upgrade to RabbitMQ 4
- Add UniqueEntity constraints to enable proper error messages in EasyAdmin
- Add custom error pages
- Update actions for new docker images
- Add commands to list/cleanup indexes
- Fix circular reference in serializer for event/tag

## [1.0.1] - 2025-01-20

- Fix upload size

## [1.0.0] - 2024-12-13

- Symfony core
- Messenger
- Lint tools
- Entity model created
- Basic feed parser structure
- Fixtures
- Typed feed configuration objects
- Feeds default mapper service
- Added phpunit tests
- Tags normalization
- Added rabbit MQ to the mix
- Added monolog package
- Added command to list feeds in the database
- Map feed items into database entities
- Change event images into own entity
- Added image download and processing
- Added geocoder services
- Moved fixtures feeds into local filesystem
- Added time service to help splitting occurrences into daily occurrences
- Added daily occurrence factory
- Added indexing service and helper commands to populate and create indexes
- Added Easy admin and event fixtures
- Make data imported from feeds read-only in easy admin
- Added event subscriber to index content created in the UI
- Added command to dump index to json for API fixture generation
- Added registration form with email verification
- Added "roles" to user create and edit forms
- Updated ITK docker templates
- Updated to PHP 8.3 og Symfony 6.4
- Added ES indexes needed to support endpoints in the API
- Updated feed import suite to support more feeds
- Updated feed mapping to support data migration
- Added migrate command fro tags from legacy db
- Updated elastic indexes to support the API
- Added PethPrefix scope to traefik rules to allow co-hosting with legacy eventdb
- Use Symfony scheduler to run periodic tasks
- Remove redundant CORS bundle
- Update FeedDefaultsMapper to set default values for empty nested properties
- Better error handling for import flow
- Force UTC for all timestamps persisted in the database
- Add `--force` option to `app:feed:import` command
- Refactor feed import to enable feed cleanup
- Handle local images
- Consolidate scheduled feed import and index populate in one command

[keep a changelog]: https://keepachangelog.com/en/1.1.0/
[unreleased]: https://github.com/itk-dev/event-database-imports/compare/1.1.6...HEAD
[1.1.6]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.6
[1.1.5]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.5
[1.1.4]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.4
[1.1.3]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.3
[1.1.2]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.2
[1.1.1]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.1
[1.1.0]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.0
[1.0.1]: https://github.com/itk-dev/event-database-imports/releases/tag/1.0.1
[1.0.0]: https://github.com/itk-dev/event-database-imports/releases/tag/1.0.0
