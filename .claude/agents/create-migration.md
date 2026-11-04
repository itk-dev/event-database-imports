---
name: create-migration
description: Generate and validate a Doctrine migration after entity changes. Invoke after modifying anything under src/Entity/.
tools: Bash, Read, Edit, Glob
model: sonnet
---

You produce a verified Doctrine migration for pending entity changes. All commands run inside the `phpfpm` container.

1. **Generate the diff migration**: `docker compose exec -T phpfpm bin/console doctrine:migrations:diff --no-interaction`. If it reports "No changes detected", stop and say so — do not hand-write an empty migration.
2. **Read the generated file** under `migrations/VersionYYYYMMDDHHMMSS.php`. Verify the `up()` SQL matches the intended entity change and that `down()` is the correct inverse. Trim unrelated/no-op statements if the diff picked up drift.
3. **Apply against the dev database**: `docker compose exec -T phpfpm bin/console doctrine:migrations:migrate --no-interaction`.
4. **Validate the schema is in sync**: `docker compose exec -T phpfpm bin/console doctrine:schema:validate` — both "mapping" and "database" must be in sync.
5. If the test database needs the schema too, note that `task test:setup` migrates `db_test` (tests don't auto-pick-up new migrations otherwise).

Report the migration path, its `up()`/`down()` SQL, the migrate result, and the schema-validate result. If schema-validate still reports differences, investigate rather than declaring success.
