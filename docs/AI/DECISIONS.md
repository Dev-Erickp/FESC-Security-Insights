# Architecture Decisions

| ID | Fecha | Decisión | Motivo | Estado |
| --- | --- | --- | --- | --- |
| ADR-001 | 2026-11-05 | Monolito modular Laravel 13 + Livewire | Equipo PHP-first; evitar SPA/React/Vue | Aceptada |
| ADR-002 | 2026-11-05 | Shared-database multi-tenancy con `organization_id` | Coste operativo menor que DB-per-tenant | Aceptada |
| ADR-003 | 2026-11-05 | Spatie Permission teams con FK `organization_id` | Roles globales FESC vs organizacionales | Aceptada |
| ADR-004 | 2026-11-05 | No crear carpetas vacías de módulos futuros | Evitar arquitectura ceremonial | Aceptada |
| ADR-005 | 2026-11-05 | Project Intelligence Index en `.project/` | Reducir lectura ciega del repo por agentes | Aceptada |
| ADR-006 | 2026-11-05 | Git Flow `develop → qa → main` + 1 rama/requerimiento | Trazabilidad y control de calidad | Aceptada |
| ADR-007 | 2026-11-05 | Contexto AI en `AGENTS.md` + `docs/AI/` + skills de proyecto | Contexto permanente sin duplicar el código | Aceptada |

Agregar filas solo para decisiones duraderas. No registrar detalles tácticos de cada PR.
