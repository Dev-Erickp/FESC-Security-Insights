---
name: fesc-testing
description: >-
  Pest testing guidance for FESC Security Insights — auth, settings, HTTP,
  authorization and future multi-tenant isolation tests. Use when writing or
  running tests.
---

# FESC Testing

## Tooling

- Pest 5 (`php artisan test`)
- Feature tests use `RefreshDatabase` + sqlite `:memory:`
- Pint for style: `php vendor/bin/pint`

## Rules

1. Cover authz and tenant isolation before happy-path polish.
2. Prefer feature tests for HTTP/Livewire flows.
3. Do not invent business fixtures for unimplemented modules.
4. After structural AI tooling changes, run Project Index tests if touched.

## Read first

- `tests/Pest.php`
- `docs/AI/DEVELOPMENT_WORKFLOW.md`
