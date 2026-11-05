# Project Context

## Qué es

**FESC Security Insights** es una plataforma SaaS multiempresa de la Fundación de Estudios Superiores Comfanorte (FESC) para gestionar el ciclo de seguridad de la información de organizaciones vinculadas:

diagnósticos → evaluaciones → controles → evidencias → hallazgos/riesgos → indicadores → informes.

## Para quién

1. **Administración FESC**: operar organizaciones, marcos, visibilidad global y gobierno.
2. **Portal de organizaciones**: usuarios de cada tenant trabajan solo sobre su contexto.

## Estado actual (verificado en código)

### Implementado

- Bootstrap Laravel 13 + Livewire starter kit (Fortify, auth, 2FA UI, passkeys, settings)
- Spatie Permission (teams/`organization_id`) y Activitylog en `User`
- Fundamentos de tenancy: `OrganizationContext` + middleware (sin módulo Organizations)
- UI administrativa base (sidebar, auth split, branding)
- Docker Compose app + MariaDB 11.6
- Project Intelligence Index (`.project/`, `project:index|query`)
- Ecosistema de contexto AI (`AGENTS.md`, `docs/AI/`, skills de proyecto)

### Parcial

- Multiempresa: contexto y permisos preparados; sin tablas `organizations` / `organization_user`
- Auditoría: paquete instalado; sin módulo Audit de negocio
- Mapa modular documentado; carpetas de dominio aún no creadas a propósito

### Planificado (no implementar aquí)

Organizations, Users (extendido), Frameworks (p. ej. ISO 27001), Assessments, Controls, Evidence, Findings, Reports, Dashboard de KPIs, Audit de negocio, Redis, MinIO, ApexCharts.

## Principios

- Shared database, aislamiento lógico por `organization_id`
- Código fuente = verdad; índices/docs orientan lectura
- Un requerimiento, una rama, un PR, validación `develop → qa → main`
