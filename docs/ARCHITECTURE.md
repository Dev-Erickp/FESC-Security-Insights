# Architecture

## Overview

FESC Security Insights is a **modular monolith** built with Laravel 13.

- One deployable application
- Domain modules under `app/Modules`
- Shared MariaDB database (shared-database multi-tenancy)
- Blade + Livewire for the UI

This avoids microservice complexity while keeping clear domain boundaries.

## High-level layers

```text
HTTP (routes / Livewire / controllers)
        ↓
Authorization (Policies, Gates, Spatie roles/permissions)
        ↓
Application services / actions
        ↓
Eloquent models + MariaDB
        ↓
Support (OrganizationContext, filesystem, queues, activity log)
```

## Modular organization

Planned domains (created only when implementation starts):

| Domain | Responsibility |
| --- | --- |
| Organizations | Tenants, membership, active organization |
| Users | User lifecycle beyond Fortify defaults |
| Frameworks | Security control catalogs |
| Assessments | Evaluation cycles |
| Controls | Control status per organization |
| Evidence | Private evidence documents |
| Findings | Nonconformities and remediation |
| Reports | Exports and report generation |
| Dashboard | Operational summaries |
| Audit | Activitylog exploration for admins |

See `app/Modules/README.md`.

## Conventional Laravel locations

Keep framework-native locations unless there is a strong reason to move them:

- `app/Models`
- `app/Http/Controllers`, `Middleware`, `Requests`
- `app/Providers`
- `app/Support`
- `database/migrations`
- `resources/views`
- `routes`

Modules add domain-specific actions/services/policies beside those conventions.

## Security foundations

- Fortify authentication, email verification, password reset, 2FA ready
- CSRF protection and verified middleware on private routes
- Database sessions
- Private local disk for sensitive files; S3/MinIO compatible later
- Spatie Permission with `organization_id` team key
- Organization context restored from session on each request

## Scalability criteria

Scale first by:

1. Clear domain modules and thin controllers
2. Queues for heavy work (reports, imports)
3. Redis for cache/queues/sessions when load requires it
4. Object storage (MinIO/S3) for evidence volume
5. Read replicas / horizontal app instances before splitting services

Do not introduce microservices or database-per-tenant unless a measured need appears.
