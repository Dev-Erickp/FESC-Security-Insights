# Work Tree (índice operativo)

Dónde trabajar según el tipo de cambio. Solo refleja lo existente + ubicaciones convencionales futuras.

## Capas transversales

| Tipo | Ubicación | Notas |
| --- | --- | --- |
| Modelos | `app/Models/` | Hoy: `User.php` |
| Migraciones | `database/migrations/` | No crear migraciones de módulos futuros sin requerimiento |
| Factories/Seeders | `database/factories/`, `database/seeders/` | |
| Actions | `app/Actions/` | Hoy Fortify |
| Support | `app/Support/` | Tenancy, ProjectIndex |
| Middleware | `app/Http/Middleware/` | `SetOrganizationContext` |
| Controllers | `app/Http/Controllers/` | Base `Controller` |
| Providers | `app/Providers/` | App + Fortify |
| Policies | `app/Policies/` | Convencional; aún no hay policies de dominio |
| Form Requests | `app/Http/Requests/` | Convencional |
| Livewire | `app/Livewire/`, `resources/views/pages/` | Pages Volt-style en settings |
| Blade | `resources/views/` | layouts, components, pages |
| Rutas | `routes/web.php`, `routes/settings.php` | |
| Config | `config/` | permission, fortify, activitylog, … |
| Tests | `tests/Feature/`, `tests/Unit/` | Pest |
| Docs AI | `docs/AI/` | Contexto permanente |
| Docs producto | `docs/` | Arquitectura, multi-tenancy |
| Docker | `docker-compose.yml`, `docker/php/` | |
| Índice | `.project/`, `app/Support/ProjectIndex/` | |

## Por dominio implementado

### Authentication

- Código: `config/fortify.php`, `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/`, `resources/views/pages/auth/`
- Tests: `tests/Feature/Auth/`
- Depende de: UI Shell, User

### Account Settings

- Código: `routes/settings.php`, `resources/views/pages/settings/`
- Tests: `tests/Feature/Settings/`

### Tenancy

- Código: `app/Support/Tenancy/OrganizationContext.php`, `app/Http/Middleware/SetOrganizationContext.php`
- Docs: `docs/MULTITENANCY.md`
- Depende de: Authorization

### Authorization / Audit

- Código: `config/permission.php`, `config/activitylog.php`, traits en `app/Models/User.php`
- Migraciones Spatie publicadas

### UI Shell

- Código: `resources/views/layouts/`, `resources/views/components/`, `resources/css/app.css`, `resources/views/dashboard.blade.php`

### Project Intelligence / AI ecosystem

- Código: `app/Support/ProjectIndex/`, `app/Console/Commands/Project*.php`
- Skills: `.cursor/skills/`
- Reglas: `.cursor/rules/`
- Docs: `AGENTS.md`, `docs/AI/`

## Dominios planificados

Al iniciar un dominio nuevo: crear carpeta bajo `app/Modules/{Domain}` solo si aporta ownership real; actualizar `Catalog.php`, `WORK_TREE.md`, `WORK_ITEMS.md` y correr `project:index --full` + `project:map`.
