# Project Intelligence Index

Decide **qué leer**, no **qué es verdad**. El código manda.

## Generar / refrescar

```bash
php artisan project:index --full
php artisan project:index --if-stale
composer index
```

## Consultar

```bash
php artisan project:query overview
php artisan project:query relevant --q="login fortify"
php artisan project:query context organization-context
php artisan project:query path --q=app/Support/Tenancy/OrganizationContext.php
php artisan project:query stale
```

STDOUT = JSON. Por defecto, `project:query` refresca si el índice está stale (excepto `stale` y `--no-refresh`).

## Stale

```bash
php artisan project:index --check
```

Razones de stale: `missing-index`, `catalog-changed`, `source-files-changed`.  
`working-tree-dirty` / `commit-changed` son warnings, no stale.

## Artefactos

| Archivo | Nivel | Commit |
| --- | --- | --- |
| `metadata.json` | 0 | sí |
| `overview.json` | 1 | sí |
| `index.json` | 2 | sí |
| `graph.json` | 3 | sí |
| `filemap.json` | 4 | sí |
| `fingerprints.json` | caché | **no** |

Catálogo fuente: `app/Support/ProjectIndex/Catalog.php`.
