# Changelog (técnico)

## 2026-11-05 — FSI-007 Ecosistema AI (en desarrollo)

- Añadidos `AGENTS.md` y `docs/AI/*` (contexto, mapas, workflow, Git, skills registry, work items, decisions, changelog).
- Skills de proyecto en `.cursor/skills/fesc-*`.
- Regla always-on `fesc-agent-workflow.mdc`.
- Comando `php artisan project:map` (`CodebaseMapper`) para regenerar `CODEBASE_MAP.md`.
- `.gitignore` permite versionar `AGENTS.md`, `.cursor/rules` y `.cursor/skills`.
- Sin cambios de módulos de negocio.

## 2026-11-05 — Project Intelligence Index

- Índice conceptual `.project/` + `project:index` / `project:query`.
- Catálogo `app/Support/ProjectIndex/Catalog.php`, hooks `.githooks/`.

## 2026-11-05 — Bootstrap inicial

- Laravel 13 Livewire starter kit, Spatie Permission/Activitylog, Docker MariaDB 11.6, docs base de arquitectura/multitenancy.
