---
name: fesc-laravel-conventions
description: >-
  Laravel 13 conventions for FESC Security Insights — Eloquent, migrations,
  Form Requests, Policies, middleware, jobs and thin controllers. Use when
  implementing or reviewing PHP/Laravel application code in this repository.
---

# FESC Laravel Conventions

## Stack

- Laravel 13, PHP 8.4+ (local may be 8.5), Pest, Fortify, Livewire 4.

## Rules

1. Controllers/Livewire thin; business logic in actions/services.
2. Validate with Form Requests or Livewire rules.
3. Authorize with Policies/Gates + Spatie permissions.
4. Migrations in `database/migrations`; English table/column names.
5. Prefer framework conventions over custom abstractions.
6. Do not invent modules under `app/Modules/` until implementation starts.

## Read first

- `AGENTS.md`
- `docs/AI/WORK_TREE.md`
- `php artisan project:query relevant --q="<tarea>"`
