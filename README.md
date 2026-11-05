# FESC Security Insights

Plataforma multiempresa de FESC para gestionar diagnósticos, evaluaciones y seguimiento de seguridad de la información de organizaciones vinculadas.

Esta fase inicial prepara el proyecto, el entorno y la arquitectura base. Los módulos de negocio se incorporarán de forma progresiva.

## Tecnologías

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.4+ (entorno local actual: PHP 8.5 compatible), Laravel 13 |
| Frontend | Blade, Livewire 4, Alpine.js (vía Livewire/Flux), Tailwind CSS 4, Vite |
| Auth | Laravel Fortify (verificación de correo, recuperación de contraseña, 2FA preparado) |
| Roles | `spatie/laravel-permission` (teams = `organization_id`) |
| Auditoría | `spatie/laravel-activitylog` |
| Base de datos | MariaDB 11.6 |
| Contenedores | Docker Compose (`app` + `mariadb`) |
| Previsto | Redis 7, MinIO, colas, scheduler, ApexCharts |

## Requisitos

- PHP 8.4+ con extensiones: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- Composer 2
- Node.js 22+ y npm
- Docker / Docker Compose (recomendado para MariaDB)
- Git

## Instalación

```bash
git clone https://github.com/Dev-Erickp/FESC-Security-Insights.git
cd FESC-Security-Insights

composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build
```

## Configuración

1. Ajusta `.env` (base de datos, correo, URL).
2. Levanta MariaDB:

```bash
docker compose up -d mariadb
```

3. Ejecuta migraciones:

```bash
php artisan migrate
```

4. (Opcional) Levanta también la aplicación en Docker:

```bash
docker compose up -d
```

Variables relevantes:

- `DB_*` — MariaDB
- `SESSION_DRIVER=database`
- `FILESYSTEM_DISK=local` (privado por defecto; S3/MinIO preparado)
- `REDIS_*` y `AWS_*` — documentados para etapas posteriores

## Ejecución local

Con PHP del host (Herd/CLI) y MariaDB en Docker:

```bash
docker compose up -d mariadb
composer run dev
```

`composer run dev` inicia servidor HTTP, cola y Vite.

Aplicación: [http://localhost:8000](http://localhost:8000)

## Calidad

```bash
vendor/bin/pint
php artisan test
npm run build
```

## Project Intelligence Index

Índice conceptual local en [`.project/`](.project/) para orientar qué leer (no qué es verdad).

```bash
php artisan project:query relevant --q="organización activa"
php artisan project:query context organization-context
php artisan project:index --check
composer index
```

Ver [`.project/README.md`](.project/README.md). Catálogo mantenido: `app/Support/ProjectIndex/Catalog.php`.

## Documentación

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md)
- [`docs/DEPENDENCIES.md`](docs/DEPENDENCIES.md)
- [`docs/MULTITENANCY.md`](docs/MULTITENANCY.md)
- [`docs/DEVELOPMENT_GUIDELINES.md`](docs/DEVELOPMENT_GUIDELINES.md)
- [`.project/README.md`](.project/README.md)

## Seguridad base incluida

- Autenticación Fortify + verificación de correo
- Recuperación de contraseñas
- CSRF y middleware de autenticación/verificación
- Sesiones en base de datos
- 2FA preparado en el starter kit
- Almacenamiento privado (`storage/app/private`)
- Contexto de organización sincronizado en servidor (sin aceptar `organization_id` arbitrario del cliente)
