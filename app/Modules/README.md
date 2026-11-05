# Domain modules

FESC Security Insights uses a modular monolith. Domains will be introduced
progressively under `app/Modules/{Domain}` without premature scaffolding.

## Planned domains

| Module | Responsibility |
| --- | --- |
| `Organizations` | Tenants, membership (`organization_user`), active organization switching |
| `Users` | User profiles, invitations, account lifecycle beyond Fortify defaults |
| `Frameworks` | Security frameworks and control catalogs (e.g. ISO 27001) |
| `Assessments` | Diagnostic and evaluation cycles |
| `Controls` | Control implementation status per organization |
| `Evidence` | Evidence files linked to controls/assessments (private storage) |
| `Findings` | Gaps, nonconformities, remediation tracking |
| `Reports` | Generated reports and exports |
| `Dashboard` | Aggregated operational views and KPIs |
| `Audit` | Application-level audit views over Spatie Activitylog |

## Incorporation rules

1. Create the module folder only when work on that domain starts.
2. Keep Laravel conventions: migrations in `database/migrations`, routes in `routes/`, views in `resources/views`.
3. Prefer services/actions inside the module for business logic; keep HTTP controllers thin.
4. Scope tenant data with `organization_id` and server-side membership checks.
5. Do not create empty placeholders for future modules.

See `docs/ARCHITECTURE.md` for the full modular strategy.
