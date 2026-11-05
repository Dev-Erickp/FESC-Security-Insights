# Git Workflow (obligatorio)

## Ramas permanentes

| Rama | Rol | Reglas |
| --- | --- | --- |
| `main` | Producción estable | Sin commits directos; solo desde `qa` aprobado |
| `qa` | Validación integral | Sin desarrollo directo; recibe desde `develop` |
| `develop` | Integración de desarrollo | Sin features directas; origen de ramas de trabajo |

Estado inspeccionado (2026-11-05): existe **solo** `main` (local + `origin/main`). `qa` y `develop` **pendientes de creación** (no creadas automáticamente por haber working tree sucio y falta de autorización explícita de push).

## Una rama por requerimiento

Formato: `<tipo>/FSI-XXX-descripcion-corta`

Tipos: `feature/`, `fix/`, `hotfix/`, `refactor/`, `test/`, `docs/`, `chore/`, `security/`

```bash
git switch develop
git pull origin develop
git switch -c feature/FSI-001-organizations
```

Prohibidos nombres genéricos (`cambios`, `pruebas`, `ajustes`, …).

## Flujo

```text
requirement branch  →  PR  →  develop  →  PR  →  qa  →  PR  →  main
```

Hotfix: desde `main` → PR a `main` → back-port a `qa` y `develop`.

## Commits

Conventional Commits, p. ej. `feat(organizations): create organization model`.

No: `cambios`, `avance`, `arreglos`.

## Pull Requests

- Requerimiento → `develop`: objetivo, alcance, pruebas, riesgos, ID FSI.
- `develop` → `qa`: validación integral.
- `qa` → `main`: aprobación humana obligatoria.

## Protección (documentada; aplicar en GitHub con autorización)

- Proteger `main`, `qa`, `develop`
- Require PR + review + checks
- Bloquear push directo y borrado de ramas permanentes

## Reglas para agentes

1. Identificar requerimiento y rama propia antes de editar.
2. Nunca trabajar en ramas permanentes.
3. No commit/push/PR/merge/delete sin autorización expresa.
4. No descartar cambios locales ajenos.
5. Si falta la rama, crearla desde `develop` o informar el bloqueo.

Registro: `docs/AI/WORK_ITEMS.md`.
