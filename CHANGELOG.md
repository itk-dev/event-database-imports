# Changelog

![keep a changelog](https://img.shields.io/badge/Keep%20a%20Changelog-v1.1.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog] for information about writing changes to this log.

## [Unreleased]

- [PR-110](https://github.com/itk-dev/event-database-imports/pull/110)
  Upgrade Elasticsearch to 8.19.18 (dev image) and the `elasticsearch/elasticsearch` client constraint to `^8.19`
- [PR-109](https://github.com/itk-dev/event-database-imports/pull/109)
  Update dev dependencies (minor/patch, in-constraint): php-cs-fixer, guzzle, phpdoc-parser, phpunit
- [PR-108](https://github.com/itk-dev/event-database-imports/pull/108)
  Scope CI image pulls per job (`--no-deps`), DB/broker-only schema validation, drop the release `--user=root`
- [PR-107](https://github.com/itk-dev/event-database-imports/pull/107)
  Add a Rector CI gate (pr.yaml) and PostToolUse auto-fix hook, and list it in the pr-readiness checks
- [PR-106](https://github.com/itk-dev/event-database-imports/pull/106)
  Guard Organization/Tag deletes against in-use records, and flash on a delete FK violation instead of a 409 page
- [PR-105](https://github.com/itk-dev/event-database-imports/pull/105)
  Broaden the Rector config (PHP, Symfony, Doctrine, PHPUnit, code-quality sets) and apply it across src/ and tests/
- [PR-104](https://github.com/itk-dev/event-database-imports/pull/104)
  Refresh README and CLAUDE docs for current tooling/CI, convert the network diagram to Mermaid, condense the changelog
- [PR-103](https://github.com/itk-dev/event-database-imports/pull/103)
  Export each index's Elasticsearch mapping to `resources/mappings/*.json` via `app:index:mappings:dump`, gated in CI
- [PR-102](https://github.com/itk-dev/event-database-imports/pull/102)
  Remove redundant npm lint tooling (`package.json`/lock); markdown and YAML linting run via docker compose services
- [PR-101](https://github.com/itk-dev/event-database-imports/pull/101)
  Upgrade cuyz/valinor 1→2 (granular casting methods, `MappingError::messages()`) with feed-mapper regression tests
- [PR-100](https://github.com/itk-dev/event-database-imports/pull/100)
  Upgrade the remaining Doctrine bundles to latest majors, drop ORM/DBAL no-op config, and update the Doctrine Flex recipes
- [PR-99](https://github.com/itk-dev/event-database-imports/pull/99)
  Upgrade Doctrine ORM 2→3 and DBAL 3→4: port UTC datetime types and raw DBAL usage, add a schema-alignment migration
- [PR-98](https://github.com/itk-dev/event-database-imports/pull/98)
  Cache the vendor directory and pre-pull images across CI workflows, and bump all GitHub Actions to their latest major
- [PR-97](https://github.com/itk-dev/event-database-imports/pull/97)
  Add Rector (task code-analysis:rector) with the Doctrine code-quality set ahead of the Doctrine 3 upgrade
- [PR-96](https://github.com/itk-dev/event-database-imports/pull/96)
  Doctrine 3 pre-work: cover the UTC datetime type and populate repository, and replace Criteria::ASC with the Order enum
- [PR-95](https://github.com/itk-dev/event-database-imports/pull/95)
  Upgrade EasyAdmin 4→5: add #[AdminDashboard], switch linkToCrud() to linkTo(), and resolve AdminContext in login
- [PR-94](https://github.com/itk-dev/event-database-imports/pull/94)
  Align the async worker on Europe/Copenhagen by removing the PHP_TIMEZONE=UTC override
- [PR-93](https://github.com/itk-dev/event-database-imports/pull/93)
  Interpret offset-less feed datetimes in the feed's declared timezone, not the worker's ambient PHP timezone
- [PR-92](https://github.com/itk-dev/event-database-imports/pull/92)
  Add EasyAdmin characterization tests ahead of the 4→5 upgrade (CRUD render, form round-trips, login gates, authz)
- [PR-91](https://github.com/itk-dev/event-database-imports/pull/91)
  Correct Danish admin translations (delete-confirmation modal and leftover English login-page strings)
- [PR-89](https://github.com/itk-dev/event-database-imports/pull/89)
  Centralize the display timezone as one injected source, and stop UTCDateTimeType mutating the caller's datetime
- [PR-88](https://github.com/itk-dev/event-database-imports/pull/88)
  Raise PHPStan to level 8 and add phpstan-strict-rules, baseline the existing findings
- [PR-87](https://github.com/itk-dev/event-database-imports/pull/87)
  Fix UserActionVoter so user management is admin-only at the URL level, and correct a wrong type assertion
- [PR-86](https://github.com/itk-dev/event-database-imports/pull/86)
  Enforce NEW-action authorization at the URL level on the CRUD voters, and scope EventVoter saves to the user's org
- [PR-85](https://github.com/itk-dev/event-database-imports/pull/85)
  Fix EventVoter so feed events cannot be edited via SAVE actions (feed guard runs before the save grant)
- [PR-84](https://github.com/itk-dev/event-database-imports/pull/84)
  Upgrade to PHPUnit 13 and tooling majors (twig-cs-fixer 4, reflection-docblock 6, phpdoc-parser 2)
- [PR-83](https://github.com/itk-dev/event-database-imports/pull/83)
  Update dependencies (minor/patch, in-constraint)
- [PR-82](https://github.com/itk-dev/event-database-imports/pull/82)
  Update dependencies to resolve security advisories (Symfony 7.4.14, Guzzle, guzzlehttp/psr7, EasyAdmin, polyfill-intl-idn)
- [PR-81](https://github.com/itk-dev/event-database-imports/pull/81)
  Add Claude Code tooling: hooks, subagents, skills, and MCP servers (context7, Symfony AI Mate)
- [PR-80](https://github.com/itk-dev/event-database-imports/pull/80)
  Document test infrastructure in README and add ADR for test database isolation
- [PR-79](https://github.com/itk-dev/event-database-imports/pull/79)
  Restore nginx APP_PATH_PREFIX rewrite so admin assets load
- [PR-78](https://github.com/itk-dev/event-database-imports/pull/78)
  Update itk docker compose templates
- [PR-76](https://github.com/itk-dev/event-database-imports/pull/76)
  Add security and admin test coverage
- [PR-75](https://github.com/itk-dev/event-database-imports/pull/75)
  Add test infrastructure (PHPUnit 12, DAMA, Liip)

## [1.2.6] - 2026-07-09

- [PR-112](https://github.com/itk-dev/event-database-imports/pull/112)
  - Fix the feed "Re-import" flash message crash by using a single, well-formed ICU plural (the `{count}`
    argument was declared with inconsistent types)
  - Add a RabbitMQ healthcheck and make the server `phpfpm`/`supervisor` services wait for it
    (`condition: service_healthy`), so `messenger:setup-transports` no longer fails on deploy before the
    AMQP listener is ready

## [1.2.5] - 2026-07-09

- [PR-111](https://github.com/itk-dev/event-database-imports/pull/111)
  - Add per-feed "convert newlines to br" option so plain-text feed descriptions keep their line breaks when
    rendered as HTML
  - Import WebP feed images by allowing `image/webp` in `ALLOWED_IMAGE_MIME_TYPES`
  - Add a "Re-import" batch action to the feed admin that force re-imports the selected feeds (async),
    mirroring `app:feed:import --force`
  - Update dependencies to resolve security advisories (Symfony 7.4.14, Guzzle, guzzlehttp/psr7, Twig,
    EasyAdmin), and audit the locked dependencies in CI (`composer audit --locked --abandoned=report`)

## [1.2.4] - 2026-05-22

- [PR-77](https://github.com/itk-dev/event-database-imports/pull/77)
  Symfony 7.4 and dependencies, CVE's on both Symfony and Twig

## [1.2.3] - 2026-03-29

- [PR-68](https://github.com/itk-dev/event-database-imports/pull/68)
  Made event organizer required for organization users

## [1.2.2] - 2025-10-07

- [PR-73](https://github.com/itk-dev/event-database-imports/pull/73)
  Set deploy user for rabbitmq container

## [1.2.1] - 2025-10-07

- [PR-72](https://github.com/itk-dev/event-database-imports/pull/72)
  Fix missing asset for file upload

## [1.2.0] - 2025-10-06

- [PR-71](https://github.com/itk-dev/event-database-imports/pull/71)
  - Run schedule feed imports as separate async jobs to avoid failures in one feed blocking others  
  - Update github actions to latest versions
- [PR-70](https://github.com/itk-dev/event-database-imports/pull/70)
  Symfony 7.3 and PHP 8.4
- [PR-69](https://github.com/itk-dev/event-database-imports/pull/69)
  Updated project template and cleaned up

## [1.1.6] - 2025-03-27

- Fix special char handling in event excerpt field, fix wrong chars in existing excerpt fields
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
[Unreleased]: https://github.com/itk-dev/event-database-imports/compare/1.2.4...HEAD
[1.2.4]: https://github.com/itk-dev/event-database-imports/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/itk-dev/event-database-imports/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/itk-dev/event-database-imports/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/itk-dev/event-database-imports/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/itk-dev/event-database-imports/compare/1.1.6...1.2.0
[1.1.6]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.6
[1.1.5]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.5
[1.1.4]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.4
[1.1.3]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.3
[1.1.2]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.2
[1.1.1]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.1
[1.1.0]: https://github.com/itk-dev/event-database-imports/releases/tag/1.1.0
[1.0.1]: https://github.com/itk-dev/event-database-imports/releases/tag/1.0.1
[1.0.0]: https://github.com/itk-dev/event-database-imports/releases/tag/1.0.0
