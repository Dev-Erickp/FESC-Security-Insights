# AGENTS.md — FESC Security Insights

Punto de entrada obligatorio para agentes de IA y desarrolladores.

## Producto

Plataforma SaaS multiempresa de FESC para diagnósticos, evaluaciones, controles, evidencias, riesgos, indicadores e informes de seguridad de la información.

## Stack verificado

- Laravel **13.40**, PHP **8.4+** (CLI local actual: 8.5.1 compatible)
- MariaDB **11.6**, Blade + Livewire **4** + Flux + Tailwind **4** + Vite
- Fortify (auth, email verify, reset, 2FA ready), Spatie Permission + Activitylog
- Pest, Pint, Docker Compose (`app` + `mariadb`)
- Project Intelligence Index (`.project/`)

## Arquitectura

- Monolito modular; shared-database multi-tenancy (`organization_id`)
- Contextos previstos: administración FESC y portal de organizaciones
- Módulos de negocio **planificados**, no inventar implementaciones inexistentes

## Antes de tocar código

1. Leer este archivo.
2. Leer `docs/AI/PROJECT_CONTEXT.md`, `ARCHITECTURE_MAP.md`, `WORK_TREE.md`, `GIT_WORKFLOW.md`.
3. Consultar `php artisan project:query relevant --q="<tarea>"` (elige qué leer; el código es la verdad).
4. Usar skills de `.cursor/skills/` solo si aplican.
5. Confirmar requerimiento, ID `FSI-XXX` y rama propia desde `develop`.

## Regla Git central

**Un requerimiento, una rama, un Pull Request, un proceso de validación.**

Flujo: `feature|fix|.../FSI-XXX-...` → `develop` → `qa` → `main`.

Prohibido desarrollar o committear directamente en `main`, `qa` o `develop`.

Sin autorización expresa del usuario: no commit, no push, no PR, no merge, no delete branches.

## Convenciones

- Código/tablas en inglés; UI en español
- Controladores delgados; Policies + Form Requests
- No microservicios; no DB-per-tenant; no Bootstrap/React/Vue
- No generar datos ficticios ni dashboards inventados

## Documentación clave

| Doc | Uso |
| --- | --- |
| `docs/AI/PROJECT_CONTEXT.md` | Qué es el producto |
| `docs/AI/ARCHITECTURE_MAP.md` | Dominios y relaciones |
| `docs/AI/CODEBASE_MAP.md` | Árbol real (`php artisan project:map`) |
| `docs/AI/WORK_TREE.md` | Dónde implementar cada cosa |
| `docs/AI/DEVELOPMENT_WORKFLOW.md` | Procedimiento A–G |
| `docs/AI/GIT_WORKFLOW.md` | Política de ramas |
| `docs/AI/WORK_ITEMS.md` | Registro FSI-XXX |
| `docs/AI/SKILLS_REGISTRY.md` | Skills disponibles |
| `docs/AI/DECISIONS.md` | Decisiones arquitectónicas |
| `docs/MULTITENANCY.md` | Aislamiento multiempresa |
| `.project/README.md` | Índice de lectura |

## Validaciones habituales

```bash
php artisan test
php vendor/bin/pint
php artisan project:index --check
php artisan project:map
```

## Herramientas de IA detectadas

- Cursor (rules + project skills)
- OpenAI Codex CLI (disponible en el entorno)
- GitHub CLI (`gh`)
- Project Intelligence Index (local)

Evitar duplicar instrucciones: este archivo remite; los detalles viven en `docs/AI/`.
