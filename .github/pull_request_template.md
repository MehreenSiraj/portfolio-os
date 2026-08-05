## What changed

<!-- A sentence or two. If it fixes an issue: "Fixes #123". -->

## Why

<!-- The problem this solves. Skip if the "what" already makes it obvious. -->

## How to verify

<!-- The click-through a reviewer should follow, or the test that proves it. -->

1.
2.

## Checks

- [ ] `vendor/bin/pint --test` passes
- [ ] `php artisan test` passes
- [ ] `npm run build` succeeds (only if you touched CSS, JS or Blade)

## Tests

- [ ] Money calculation changed → there is a test covering the arithmetic
- [ ] Approval flow changed → there is a test covering who can approve, the resulting state, and who is blocked
- [ ] Neither applies

## Constraints

Confirm the ones relevant to this change:

- [ ] Works with `database` or `file` session, cache and queue drivers — no Redis, no daemon, no websockets
- [ ] Any new queued job is safe to run late, out of order, or twice
- [ ] Monetary values stay integer minor units, read through `Money` / `Currency` — no hardcoded currency code, symbol or exponent
- [ ] No new hard delete of a financial record, and no edit path to an approved distribution run
- [ ] Project-scoped queries filter by the user's assignments at the query level
- [ ] No credential, password, key or bank detail can reach an AI provider
- [ ] Existing migrations were not modified; schema changes are new migration files

## Deployment impact

Deploys are manual FTP uploads, so please be explicit:

- [ ] Adds a migration
- [ ] Adds or changes an `.env` key (list it below)
- [ ] Changes Composer dependencies → operators must re-upload `vendor/`
- [ ] Changes frontend assets → operators must re-upload the built `public/` output
- [ ] None of the above

<!-- Notes for whoever deploys this: -->
