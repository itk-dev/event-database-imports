# Test database isolation

Date: 06-07-2026

## Status

Accepted

## Context

The functional test suite exercises the application against a real database:
tests boot the kernel, load fixtures through
[Liip TestFixturesBundle](https://github.com/liip/LiipTestFixturesBundle) and
assert on rendered responses and persisted state. To keep such tests fast and
independent, each test is wrapped in a database transaction that is rolled back
on teardown by [DAMA DoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle)
(wired as a PHPUnit extension in `phpunit.xml.dist`). Because the rollback
undoes every write, the tests never need to drop and recreate the schema —
`config/packages/test/liip_test_fixtures.yaml` sets `keep_database_and_schema: true`
so Liip reuses the existing schema rather than fighting DAMA.

That leaves one decision: *which* database the suite connects to. In the local
Docker stack the application user (`db`) is granted only
`ALL PRIVILEGES ON \`db\`.*` — it cannot `CREATE DATABASE`. This constrains the
options:

1. **Run against the shared dev database (`db`).** No extra schema to provision,
   but a local test run then loads fixtures into — and (should DAMA ever be
   disabled or misconfigured) can permanently purge — the developer's own dev
   data. It also rules out parallel execution.
2. **Dedicated `db_test` database, provisioned by root.** The root credentials
   ship in `docker-compose.yml` (fixed local-stack values), so a separate
   database can be created once out-of-band and granted to the `db` user. Tests
   then run fully isolated from dev data.
3. **Grant the `db` user global `CREATE`, restore the ParaTest suffix.** Enables
   [ParaTest](https://github.com/paratestphp/paratest)'s per-worker
   `db_test<token>` databases, but widens the app user's privileges beyond what
   production grants and diverges the local stack from the deployed one.
4. **A dedicated MariaDB test container.** Cleanest isolation, but adds a service
   to a template-managed `docker-compose.yml` and more moving parts than the
   suite needs today.

## Decision

Run the suite against a dedicated `db_test` database (option 2).

`config/packages/doctrine.yaml` (`when@test`) sets `dbname_suffix: '_test'`, so
the test environment connects to `<dbname>_test` (`db_test`) instead of the dev
`db`. The schema is provisioned by the `test:setup` Task, which creates and
grants the database as root (idempotent) and then migrates it:

```yaml
# Taskfile.yml
test:setup:
  cmds:
    - >-
      {{.DOCKER_COMPOSE}} exec -T mariadb mariadb -uroot -ppassword
      -e 'CREATE DATABASE IF NOT EXISTS db_test; GRANT ALL PRIVILEGES ON `db_test`.* TO `db`@`%`;'
    - task: console
      vars:
        CONSOLE_ARGS: --env=test doctrine:migrations:migrate --no-interaction
```

`task test` runs `test:setup` before PHPUnit, and `task site:update` runs it as
part of local setup, so provisioning is automatic and cannot be a forgotten
manual step. DAMA's per-test transaction rollback continues to isolate
individual tests within `db_test`.

Option 3 was rejected because it grants the local app user privileges the
production user does not have; option 4 because a whole extra container is more
than the current suite size warrants. Both remain viable if the constraints
change (see below).

## Consequences

The dev database can never be touched by a test run — even if DAMA is disabled
or a test commits mid-transaction, the blast radius is `db_test`, not `db`. The
suite is deterministic and fast: schema is migrated once and every test rolls
back.

The trade-off is that the test database name is fixed (`_test` with no
`TEST_TOKEN` suffix), so **parallel execution with ParaTest is not supported** —
multiple workers would collide on the single `db_test` schema. Re-enabling it
would require per-worker databases, i.e. option 3 (global `CREATE` grant) or
pre-creating `db_test1..N`. At the current suite runtime (a few seconds) this is
not a limiting factor.

Provisioning depends on the root credentials in `docker-compose.yml`; if those
change, `test:setup` must be updated to match. CI provisions `db_test` the same
way — `task test` runs `test:setup` on the ephemeral CI database, so no separate
migration step is needed in the workflow.
