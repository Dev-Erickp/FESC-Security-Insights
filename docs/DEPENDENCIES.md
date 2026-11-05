# Dependencies

## Runtime / platform

| Dependency | Version (verified at bootstrap) | Purpose |
| --- | --- | --- |
| PHP | 8.5.1 local CLI (compatible; project targets 8.4+; Docker image uses 8.4) | Runtime |
| Laravel Framework | 13.40.0 | Application framework |
| MariaDB | 11.6.2 (`mariadb:11.6`) | Primary database |
| Composer | 2.10.1 | PHP dependency manager |
| Node.js / npm | 25.6.0 / 11.8.0 | Frontend tooling |

## Backend packages

| Package | Version | Purpose | Justification |
| --- | --- | --- | --- |
| `laravel/framework` | 13.40.0 | Core framework | Required |
| `laravel/fortify` | 1.41.0 | Auth backend | Starter kit authentication |
| `livewire/livewire` | 4.4.8 | Reactive Blade UI | Official Livewire stack |
| `livewire/flux` | ^2.13 | UI components | Included by Livewire starter kit |
| `livewire/blaze` | ^1.0 | Livewire tooling | Included by starter kit |
| `laravel/chisel` | ^0.1 | Starter kit support | Included by starter kit |
| `spatie/laravel-permission` | 8.4.0 | Roles and permissions | Global vs organization scopes via teams/`organization_id` |
| `spatie/laravel-activitylog` | 5.1.3 | Activity trail | Foundational audit readiness (v5 namespaces) |
| `laravel/tinker` | ^3.0 | REPL | Standard Laravel tooling |

## Frontend packages

| Package | Purpose |
| --- | --- |
| Tailwind CSS 4 | Styling |
| Vite / vite-plus | Asset bundling |
| Alpine.js | Included through Livewire/Flux (no duplicate install) |
| `@laravel/passkeys` | Passkey support from starter kit |

ApexCharts is intentionally deferred to a later phase.

## Quality packages

| Package | Purpose |
| --- | --- |
| Laravel Pint | Code style |
| Pest 5 | Feature suite (selected by starter kit) |
| Larastan | Static analysis (available via `composer types:check`) |

## Intentionally not installed yet

- Multi-tenancy packages that create one database per tenant
- Redis client libraries beyond PHP redis extension readiness
- MinIO SDKs beyond Laravel S3 disk configuration
- ApexCharts
- Bootstrap / React / Vue

## Composer conflict check

Run:

```bash
composer validate
composer install --dry-run
```

Bootstrap install completed without dependency conflicts for Spatie Permission 8.4.0 and Activitylog 5.1.3 on Laravel 13.
