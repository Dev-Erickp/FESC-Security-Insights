# Architecture Map

## Vista general

```text
UI (Blade/Livewire/Flux)
        ↓
HTTP / Middleware (auth, verified, SetOrganizationContext)
        ↓
Policies + Spatie roles/permissions (global vs organization_id)
        ↓
Actions / Services (futuro por dominio)
        ↓
Eloquent + MariaDB (shared schema)
        ↓
Support (OrganizationContext, ProjectIndex, Filesystem)
```

## Dominios

| Dominio | Estado | Responsabilidad |
| --- | --- | --- |
| Authentication | Implementado | Fortify, login/registro/reset/2FA/passkeys |
| Account Settings | Implementado | Perfil, apariencia, seguridad |
| Tenancy | Parcial | Contexto de organización activa |
| Authorization | Implementado (base) | Spatie Permission teams |
| Audit | Parcial | Activitylog configurado |
| UI Shell | Implementado | Layouts, branding, dashboard stub |
| Infrastructure | Implementado | Docker, Vite, bootstrap, config |
| Project Intelligence | Implementado | Índice + mapas AI |
| Organizations+ | Planificado | Ver `app/Modules/README.md` |

## Relaciones clave

- `User` → HasRoles + LogsActivity + 2FA
- `OrganizationContext` ↔ Spatie `setPermissionsTeamId(organization_id)`
- Rutas privadas: `auth` + `verified`
- Evidence futura: disk privado / S3-MinIO path-style

## Escalado previsto

Queues → Redis; archivos → MinIO; módulos en `app/Modules/{Domain}` solo al implementar.

Detalle narrativo: `docs/ARCHITECTURE.md`, `docs/MULTITENANCY.md`.
