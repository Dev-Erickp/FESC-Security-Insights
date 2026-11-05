# Multitenancy

## Strategy

**Shared database, shared schema.**

- One MariaDB database
- Tenant discriminator: `organization_id`
- Users may belong to many organizations through `organization_user` (planned)
- Active organization stored in the authenticated session

This matches FESC’s model of multiple linked organizations without the operational cost of database-per-tenant.

## Planned schema (not migrated yet)

```text
organizations
  id, name, slug, status, timestamps

organization_user
  id, organization_id, user_id, role_in_org?, timestamps
  unique(organization_id, user_id)

tenant-owned tables (future modules)
  ..., organization_id, ...
```

No organization migrations are created in this bootstrap phase.

## Active organization context

`App\Support\Tenancy\OrganizationContext`:

- Reads/writes `active_organization_id` in session
- Syncs Spatie Permission team id via `setPermissionsTeamId()`
- Must never trust a raw `organization_id` from query/body/headers

Middleware `SetOrganizationContext` restores the Spatie team context on every web request.

## Permission model

Spatie Permission teams are enabled:

- Foreign key: `organization_id`
- `organization_id = null` → global FESC roles/permissions
- `organization_id = <id>` → organization-scoped roles/permissions

Spatie does **not** enforce membership by itself. Application code must:

1. Verify the user belongs to the organization before setting context
2. Authorize actions with policies/permissions inside that context
3. Scope Eloquent queries by `organization_id`

## Security rules

1. Switching organization is a server-validated action, not a client-chosen filter.
2. Unauthorized organization IDs must result in 403/404, never silent data leakage.
3. Global FESC operators use explicit global permissions; they are not implied by tenant roles.
4. Private evidence files must be authorized against the active organization and ownership.
5. Activitylog should record actor + organization context when modules land.

## Future implementation sequence

1. Organizations module + migrations + membership
2. Switch-organization action with membership check
3. Global Eloquent scope / query helpers for `organization_id`
4. Seed global and organizational roles
5. Policies that combine membership + permission checks
