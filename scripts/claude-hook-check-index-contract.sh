#!/bin/sh
# Stop-hook helper: warn when the Elasticsearch index contract changed.
#
# event-database-imports WRITES the ES indices; event-database-api READS them.
# The contract (index-name enum + document mappings) is duplicated by hand in
# both repos with no compile-time link, so a change here silently breaks the API.
# See CLAUDE.md -> "Works with event-database-api".
set -u

cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0

CHANGED="$(git status --porcelain 2>/dev/null | awk '{print $NF}')"

echo "$CHANGED" | grep -qE '^src/Model/Indexing/(IndexNames\.php|Mappings/)' || exit 0

cat >&2 <<'MSG'
WARN: the Elasticsearch index contract changed (src/Model/Indexing/IndexNames.php
      or src/Model/Indexing/Mappings/). event-database-api reads these indices and
      duplicates the contract by hand:
        - keep its src/Model/IndexName.php index-name enum in sync, and
        - a renamed/retyped mapping field silently breaks its API filters/providers
          (it has no mapping definitions of its own).
      Coordinate the change with event-database-api before indexing.
MSG

exit 0
