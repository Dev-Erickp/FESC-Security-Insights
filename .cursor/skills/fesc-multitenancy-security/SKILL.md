---
name: fesc-multitenancy-security
description: >-
  Shared-database multi-tenancy and security rules for FESC Security Insights.
  Use when touching organization_id, permissions, membership, private files,
  IDOR risks, sessions, or tenant isolation.
---

# FESC Multitenancy & Security

## Model

- One MariaDB database; tenant key `organization_id`.
- Spatie Permission teams enabled with `organization_id`.
- Active org in session via `OrganizationContext` — never trust client-supplied IDs.

## Mandatory checks

1. Verify membership server-side before setting organization context.
2. Scope queries by `organization_id`.
3. Combine membership + permission/policy checks.
4. Private evidence only through authorized routes.
5. Prefer 403/404 over leaking cross-tenant data.

## Read first

- `docs/MULTITENANCY.md`
- `docs/AI/ARCHITECTURE_MAP.md`
- `app/Support/Tenancy/OrganizationContext.php`
- `config/permission.php`
