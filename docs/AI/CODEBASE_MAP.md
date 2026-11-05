# Codebase Map

Árbol **real** del repositorio. Generado automáticamente. No inventa módulos inexistentes.

- Generado: `2026-11-05T20:29:05+00:00`
- Directorios: `71`
- Archivos: `175`
- Comando: `php artisan project:map`

Excluye: `vendor/`, `node_modules/`, `.git/`, `storage/framework/`, secretos (`.env`), temporales y cachés.

```text
FESC Security Insights/
├── .cursor/
│   ├── rules/
│   │   ├── fesc-agent-workflow.mdc
│   │   └── project-intelligence.mdc
│   └── skills/
│       ├── fesc-architecture/
│       │   └── SKILL.md
│       ├── fesc-git-flow/
│       │   └── SKILL.md
│       ├── fesc-laravel-conventions/
│       │   └── SKILL.md
│       ├── fesc-livewire-blade/
│       │   └── SKILL.md
│       ├── fesc-multitenancy-security/
│       │   └── SKILL.md
│       └── fesc-testing/
│           └── SKILL.md
├── .githooks/
│   ├── post-checkout
│   ├── post-commit
│   └── post-merge
├── .github/
│   ├── workflows/
│   │   └── tests.yml
│   └── dependabot.yml
├── .project/
│   ├── README.md
│   ├── filemap.json
│   ├── graph.json
│   ├── index.json
│   ├── metadata.json
│   └── overview.json
├── app/
│   ├── Actions/
│   │   └── Fortify/
│   │       ├── CreateNewUser.php
│   │       └── ResetUserPassword.php
│   ├── Concerns/
│   │   ├── PasswordValidationRules.php
│   │   └── ProfileValidationRules.php
│   ├── Console/
│   │   └── Commands/
│   │       ├── ProjectIndexCommand.php
│   │       ├── ProjectMapCommand.php
│   │       └── ProjectQueryCommand.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Controller.php
│   │   └── Middleware/
│   │       └── SetOrganizationContext.php
│   ├── Livewire/
│   │   └── Actions/
│   │       └── Logout.php
│   ├── Models/
│   │   └── User.php
│   ├── Modules/
│   │   └── README.md
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── FortifyServiceProvider.php
│   └── Support/
│       ├── ProjectIndex/
│       │   ├── Catalog.php
│       │   ├── CodebaseMapper.php
│       │   ├── GitHookInstaller.php
│       │   ├── IndexQuery.php
│       │   ├── IndexStore.php
│       │   ├── Indexer.php
│       │   └── PhpImportScanner.php
│       └── Tenancy/
│           └── OrganizationContext.php
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
│   ├── activitylog.php
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── fortify.php
│   ├── logging.php
│   ├── mail.php
│   ├── permission.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2024_01_01_000000_create_passkeys_table.php
│   │   ├── 2025_08_14_170933_add_two_factor_columns_to_users_table.php
│   │   ├── 2026_11_05_152607_create_permission_tables.php
│   │   └── 2026_11_05_152608_create_activity_log_table.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php
│   ├── .gitignore
│   └── database.sqlite
├── docker/
│   └── php/
│       └── Dockerfile
├── docs/
│   ├── AI/
│   │   ├── ARCHITECTURE_MAP.md
│   │   ├── CHANGELOG.md
│   │   ├── DECISIONS.md
│   │   ├── DEVELOPMENT_WORKFLOW.md
│   │   ├── GIT_WORKFLOW.md
│   │   ├── PROJECT_CONTEXT.md
│   │   ├── SKILLS_REGISTRY.md
│   │   ├── WORK_ITEMS.md
│   │   └── WORK_TREE.md
│   ├── ARCHITECTURE.md
│   ├── DEPENDENCIES.md
│   ├── DEVELOPMENT_GUIDELINES.md
│   └── MULTITENANCY.md
├── lang/
│   ├── en/
│   │   ├── auth.php
│   │   ├── pagination.php
│   │   ├── passwords.php
│   │   └── validation.php
│   └── es.json
├── public/
│   ├── .htaccess
│   ├── apple-touch-icon.png
│   ├── favicon.ico
│   ├── favicon.svg
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── passkeys.js
│   └── views/
│       ├── components/
│       │   ├── app-logo-icon.blade.php
│       │   ├── app-logo.blade.php
│       │   ├── auth-header.blade.php
│       │   ├── auth-session-status.blade.php
│       │   ├── desktop-user-menu.blade.php
│       │   ├── passkey-registration.blade.php
│       │   ├── passkey-verify.blade.php
│       │   └── placeholder-pattern.blade.php
│       ├── flux/
│       │   ├── icon/
│       │   │   ├── book-open-text.blade.php
│       │   │   ├── chevrons-up-down.blade.php
│       │   │   ├── folder-git-2.blade.php
│       │   │   └── layout-grid.blade.php
│       │   └── navlist/
│       │       └── group.blade.php
│       ├── layouts/
│       │   ├── app/
│       │   │   ├── header.blade.php
│       │   │   └── sidebar.blade.php
│       │   ├── auth/
│       │   │   ├── card.blade.php
│       │   │   ├── simple.blade.php
│       │   │   └── split.blade.php
│       │   ├── app.blade.php
│       │   └── auth.blade.php
│       ├── pages/
│       │   ├── auth/
│       │   │   ├── confirm-password.blade.php
│       │   │   ├── forgot-password.blade.php
│       │   │   ├── login.blade.php
│       │   │   ├── register.blade.php
│       │   │   ├── reset-password.blade.php
│       │   │   ├── two-factor-challenge.blade.php
│       │   │   └── verify-email.blade.php
│       │   └── settings/
│       │       ├── two-factor/
│       │       │   └── ⚡recovery-codes.blade.php
│       │       ├── layout.blade.php
│       │       ├── ⚡appearance.blade.php
│       │       ├── ⚡delete-user-form.blade.php
│       │       ├── ⚡delete-user-modal.blade.php
│       │       ├── ⚡profile.blade.php
│       │       ├── ⚡security.blade.php
│       │       └── ⚡two-factor-setup-modal.blade.php
│       ├── partials/
│       │   ├── head.blade.php
│       │   └── settings-heading.blade.php
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── console.php
│   ├── settings.php
│   └── web.php
├── storage/
│   └── app/
│       ├── private/
│       │   └── .gitignore
│       ├── public/
│       │   └── .gitignore
│       └── .gitignore
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── AuthenticationTest.php
│   │   │   ├── EmailVerificationTest.php
│   │   │   ├── PasswordConfirmationTest.php
│   │   │   ├── PasswordResetTest.php
│   │   │   ├── RegistrationTest.php
│   │   │   └── TwoFactorChallengeTest.php
│   │   ├── ProjectIndex/
│   │   │   ├── ProjectIndexCommandsTest.php
│   │   │   └── ProjectMapCommandTest.php
│   │   ├── Settings/
│   │   │   ├── ProfileUpdateTest.php
│   │   │   └── SecurityTest.php
│   │   ├── DashboardTest.php
│   │   └── ExampleTest.php
│   ├── Unit/
│   │   ├── ProjectIndex/
│   │   │   ├── IndexerTest.php
│   │   │   └── PhpImportScannerTest.php
│   │   └── ExampleTest.php
│   ├── Pest.php
│   └── TestCase.php
├── .dockerignore
├── .editorconfig
├── .env.example
├── .gitattributes
├── .gitignore
├── .npmrc
├── AGENTS.md
├── README.md
├── artisan
├── composer.json
├── composer.lock
├── docker-compose.yml
├── package-lock.json
├── package.json
├── phpstan.neon
├── phpunit.xml
├── pint.json
└── vite.config.js
```

