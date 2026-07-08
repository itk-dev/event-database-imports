# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment: everything runs in Docker

PHP, Composer, `bin/console`, and PHPUnit are **not** available on the host — they run inside the `phpfpm`
container. Use the `task` wrappers (or `docker compose exec phpfpm …`) for all such commands; running them
directly on the host will fail.

The stack is `docker compose` (mariadb, phpfpm, nginx, rabbit, elasticsearch, mail). `docker-compose.override.yml`
is auto-loaded and provides local-only settings (e.g. it passes `APP_PATH_PREFIX` to nginx).

## Common commands

```shell
task site:update                 # full setup: pull, up, composer install, migrate, index:create, messenger:setup-transports, provision test DB
task test                        # run the whole PHPUnit suite (runs test:setup first, then phpunit with coverage)
task test:setup                  # provision + migrate the isolated db_test database (idempotent)
task code-analysis:phpstan       # PHPStan (config: phpstan.dist.neon)
task code-analysis:rector        # Rector (dry-run; :rector:apply to apply)
task coding-standards:check      # markdown + PHP (php-cs-fixer) + twig + yaml, check only
task coding-standards:apply      # auto-fix the above
task console -- <cmd>            # Symfony console, e.g. task console -- app:feed:list
task composer -- <args>         # Composer, e.g. task composer -- require foo/bar
task fixtures:load               # load dev fixtures (App\DataFixtures\*)
```

Run a single test / subset (inside the container):

```shell
docker compose exec phpfpm vendor/bin/phpunit --filter EventVoterTest
docker compose exec phpfpm vendor/bin/phpunit tests/Functional/Admin/EventCrudTest.php
docker compose exec phpfpm vendor/bin/phpunit --testsuite unit        # or: functional
```

Async workers (messages are processed out-of-process; nothing happens until a consumer runs):

```shell
task console -- messenger:consume async scheduler_default -vvv   # or: composer run queues
```

## Architecture

This is the **import/write + admin** side of the event database (the public read API lives in the separate
`event-database-api` repo). Architectural decisions are recorded in `docs/adr/`; read those before changing
the areas they cover.

### CQRS: write model vs. read model (ADR 002)

The write model is Doctrine entities in MariaDB (`src/Entity`, `src/Repository`). The read model is an
ElasticSearch index (`src/Service/Indexing`, `src/Model/Indexing`) consumed by the frontend API. Writing data
and serving data are deliberately decoupled — changes to import/parsing must not impact the read side. Indexing
happens asynchronously via `IndexMessage`; commands `app:index:create|populate|purge|dump|list` manage indexes.

### Asynchronous pipeline (ADR 003)

Import is a chain of Symfony Messenger messages, each with a matching handler in `src/MessageHandler`, routed to
a RabbitMQ `async` transport (`config/packages/messenger.yaml`). Each step is independent and parallelizable:

```text
ReadFeedMessage → FeedItemDataMessage → FeedItemNormalizationMessage → EventMessage
                                                                          ├→ DailyOccurrenceMessage
                                                                          ├→ GeocoderMessage
                                                                          ├→ ImageMessage
                                                                          └→ IndexMessage
```

Consequence: work is asynchronous — when tracing "why didn't X happen", check whether a consumer is running and
whether the message reached the `failed` transport (`app:messenger:purge-failed`). Feed imports are triggered by
`app:feed:import` / scheduled via `app:schedule:import` (Symfony Scheduler).

### Typed mapping (ADR 004)

Incoming feed data (varying JSON/XML quality) is mapped into strictly-typed objects with **Valinor** during
normalization (`src/Model/Feed`, `src/Service/Feeds`). Prefer these typed objects over associative arrays when
moving data through the pipeline.

### Admin UI & authorization (ADR 005, 006)

The admin UI is **EasyAdmin** (`src/Controller/Admin`, custom fields/filters in `src/EasyAdmin`), served under
the `APP_PATH_PREFIX` path (`/admin`) because it co-hosts with the read API. Authorization is enforced by voters
in `src/Security/Voter` (per-entity: Event, Organization, Location, Address, Tag, plus User voters), keyed on
EasyAdmin's `EA_EXECUTE_ACTION` permission and the action name. Two users exist: Symfony super-admins and MitID
users invited per organization; organization editors may only edit non-feed events belonging to their org.

### Other conventions

- **Feed-imported content is read-only in the admin** (ADR 007) — it can only change via re-import.
- **All datetimes are stored in UTC** via custom Doctrine types (`src/Doctrine/Extensions`, ADR 008); view layers
  (API serialization, EasyAdmin field/filter configurators) must apply the display timezone.

## Testing (ADR 009)

PHPUnit 13, split into `tests/Unit` (no DB) and `tests/Functional` (boots the kernel + DB). Isolation is via
**DAMA** (each test wrapped in a rolled-back transaction) + **Liip** fixtures, running against a dedicated
`db_test` database provisioned by `task test:setup` — never the dev `db`. Lightweight functional fixtures live in
`tests/Fixtures` (`TestUserFixtures`, `TestEventFixtures`), independent of the full `EventFixture` chain.

Functional tests hit the Symfony kernel directly (BrowserKit) and bypass nginx. The one exception is
`tests/Functional/Smoke/NginxAdminAssetSmokeTest.php`, which makes a real HTTP request to the nginx container to
verify the `APP_PATH_PREFIX` asset rewrite; it skips when the stack/assets aren't available.

## Git / PR conventions

Conventional Commits (`feat:`, `fix:`, `docs:`, `test:`, …). Each PR adds a wrapped `- [PR-N](url)\n  Description`
line at the top of the `[Unreleased]` section in `CHANGELOG.md` (PR number filled in once the PR exists), ordered
by descending PR number. The `changelog-entry` skill (`/changelog-entry`) handles the format.

## Works with event-database-api

This repo is the **write/admin** side; [`event-database-api`](https://github.com/itk-dev/event-database-api) is the
public **read-only** API. They are decoupled at runtime and communicate only through a **shared Elasticsearch
cluster** — no shared database, no HTTP call between them.

- **This repo writes; the API reads.** Feed import → normalize → persist (MariaDB) → index into ES
  (`src/Service/Indexing/AbstractIndexingElastic.php`, `INDEXING_URL`). The API serves `/api/v2/…` by reading the
  same ES indices; it has **no domain database**.
- **This repo owns the index lifecycle.** `app:index:create|populate|purge` build a versioned index
  `<alias>_<timestamp>`, then atomically repoint the alias. The API always queries the **alias** — so it never sees
  a half-built index, but it also breaks if an alias was never populated. The API must never write to ES.
- **The contract is hand-duplicated, with no compile-time link:**
  - Index names — `src/Model/Indexing/IndexNames.php` here ↔ `src/Model/IndexName.php` in the API
    (`events`, `organizations`, `occurrences`, `daily_occurrences`, `tags`, `vocabularies`, `locations`).
  - Document shape — mappings are authored **only here** (`src/Model/Indexing/Mappings/`) and exported to committed
    JSON at `resources/mappings/*.json` via `app:index:mappings:dump` (`task index:mappings:dump`). The API has no
    mappings of its own and trusts the fields/types this repo writes; a renamed or retyped field silently breaks the
    API's filters/providers.
  - Keep both in sync when changing either. The `index-mappings` CI gate fails if a mapping class changed without the
    export being regenerated, and the `Stop` hook (below) warns locally when the enum or mappings change.
- **Co-hosted by path prefix** in production: `/admin/` → this app (`APP_PATH_PREFIX`), `/api/v2/` → the API, via
  Traefik on the shared `frontend` network.

## Claude Code automation

`.claude/settings.json`, `.claude/agents/`, `.claude/skills/`, and `.mcp.json` configure this repo's Claude Code
setup. All hooks and MCP servers run tooling **inside the `phpfpm` container**.

- **Hooks** — `SessionStart` boots the stack and checks host prerequisites; `PostToolUse` auto-runs php-cs-fixer,
  phpstan, twig-cs-fixer, `composer normalize`, prettier, and markdownlint on the file you just edited (so
  single-file changes don't need manual formatting); `PreToolUse` blocks edits to generated/locked/secret files
  (`config/reference.php`, lock files, `.env.local`, `phpstan-baseline.neon`, …); `Stop` validates the DI container
  (`lint:container`) and warns on ES index-contract changes.
- **Prerequisite:** `jq` must be installed on the **host** — the Edit/Write hooks read the edited file path from the
  tool payload via `jq` and silently no-op without it (`brew install jq`).
- **Subagents** (`.claude/agents/`): `pr-readiness` (run all CI-equivalent checks), `create-migration`
  (Doctrine diff + validate), `authorization-reviewer` (EasyAdmin voter layer), `messenger-handler-reviewer`
  (async pipeline).
- **Skills** (`.claude/skills/`, user-invocable): `/changelog-entry`, `/new-message`.
- **MCP servers** (`.mcp.json`): `context7` (library docs) and `symfony-ai-mate` — Symfony AI Mate runs in the
  `phpfpm` container and exposes service-container introspection, Symfony profiler access, and Monolog log search.
  After changing services or Mate extensions, re-run `docker compose exec phpfpm vendor/bin/mate discover`.
  PhpStorm's MCP (symbol info, inspections, Xdebug) is also available when the IDE is open — no repo config needed.
