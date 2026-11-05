# Development guidelines

## Language conventions

- Code, classes, methods, tables, columns: **English**
- User interface copy: **Spanish**
- Technical comments only when they clarify non-obvious intent

## Code structure

- Thin controllers / Livewire components
- Validation in Form Requests (or Livewire rules)
- Authorization in Policies and Gates (+ Spatie permissions)
- Business logic in actions/services, not in controllers
- Prefer Eloquent relationships and explicit query scopes
- Avoid duplicate logic and premature abstractions
- Prefer Laravel conventions over custom frameworks

## Modular growth

1. Start work in an existing conventional location when possible.
2. Promote a domain into `app/Modules/{Domain}` when it gains coherent ownership.
3. Do not create empty module folders “for later”.
4. Keep migrations centralized in `database/migrations`.

## Security checklist for every feature

- Authenticated + verified where needed
- Policy/permission check on the server
- Tenant membership validated before using `organization_id`
- No mass-assignment of tenant identifiers from request input
- Private files served through authorized routes

## Testing

- Pest is the project test runner
- Prefer feature tests for auth and HTTP flows
- Add unit tests for pure domain services
- When Organizations land, add membership isolation tests first

Useful commands:

```bash
php artisan test
vendor/bin/pint
composer run types:check
npm run build
```

## Development flow

1. Pull latest `main`
2. `composer install && npm install`
3. Ensure MariaDB is running (`docker compose up -d mariadb`)
4. `php artisan migrate`
5. Create a feature branch
6. Implement with tests
7. Run Pint + Pest + frontend build
8. Open PR (no force-push to main)

## Infrastructure readiness

| Service | Status |
| --- | --- |
| MariaDB 11.6 | Active via Docker Compose |
| App container (PHP 8.4) | Available via Docker Compose |
| Redis 7 | Documented, deferred |
| MinIO | Documented via S3 disk env vars, deferred |
| Queues | `database` driver ready; Redis later |
| Scheduler | Laravel scheduler available when cron/container process is added |

## Visual identity

- Accent: deep teal `#0f4c5c`
- Typography: Source Sans 3
- Admin shell: sidebar + top bar on mobile
- Tone: secure, sober, institutional, clear
