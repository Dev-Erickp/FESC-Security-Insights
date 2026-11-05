# Development Workflow

Procedimiento obligatorio antes y durante cada requerimiento.

## A. Contextualización

Leer: `AGENTS.md` → `PROJECT_CONTEXT.md` → `ARCHITECTURE_MAP.md` → `WORK_TREE.md` → `GIT_WORKFLOW.md` → skills relevantes.

## B. Identificación

- Requerimiento + ID `FSI-XXX` (`WORK_ITEMS.md`)
- Rama `<tipo>/FSI-XXX-descripcion` desde `develop`
- Módulo, dependencias, decisiones aplicables (`DECISIONS.md`)

## C. Exploración

```bash
php artisan project:query relevant --q="<tarea>"
php artisan project:query context <node-id>
```

Buscar implementaciones existentes antes de crear nuevas.

## D. Análisis

Presentar comprensión, rama, archivos, cambios, riesgos y pruebas.

## E. Implementación

Solo en la rama del requerimiento. Sin alcance colateral.

## F. Validación

```bash
php artisan test
php vendor/bin/pint
# si aplica:
php artisan project:index --check
php artisan migrate --pretend
```

## G. Documentación

Actualizar solo docs afectados: `WORK_ITEMS`, `CHANGELOG`, `DECISIONS`, `WORK_TREE`/`ARCHITECTURE_MAP` si cambió estructura, `project:map` si cambió el árbol.

## Optimización de tokens

No leer el repo entero. No regenerar mapas sin cambios estructurales. No duplicar docs.
