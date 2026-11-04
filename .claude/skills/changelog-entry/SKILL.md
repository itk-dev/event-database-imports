---
name: changelog-entry
description: Add or fix this repo's CHANGELOG.md entry for the current PR, in the exact house format. Use before opening a PR or when resolving a CHANGELOG merge conflict.
disable-model-invocation: true
---

Add a changelog line for the current work to `CHANGELOG.md`, following this repo's convention exactly (getting it wrong causes the `changelog` CI check to fail and repeated merge conflicts).

## Format

Under the `## [Unreleased]` heading, entries are a **two-line wrapped** list item:

```
- [PR-N](https://github.com/itk-dev/event-database-imports/pull/N)
  Short imperative description of the change
```

Rules:

- One entry per PR. Ordered by **descending PR number** (newest on top).
- The URL is always the `event-database-imports` repo (not `event-database-api`).
- Keep the description to one wrapped line; match the tone of surrounding entries. Line length ≤ 120 (markdownlint MD013).

## Two-step PR number

The PR number isn't known until the PR exists. So:

1. Add the entry now with a `PR-XX` placeholder and `pull/XX` URL.
2. After the PR is opened (`gh pr view --json number`), replace `XX` with the real number in a follow-up commit (`docs: set PR number in CHANGELOG entry`).

## Steps

1. `git fetch origin develop` and read the current `[Unreleased]` block so you insert in the right order and don't duplicate.
2. Insert the entry at the correct descending-number position.
3. Lint: `docker compose run --rm markdownlint markdownlint CHANGELOG.md`.
4. If a PR already exists, set the real number; otherwise leave `XX` and remind the user of step 2.
