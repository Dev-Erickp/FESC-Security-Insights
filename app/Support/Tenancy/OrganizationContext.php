<?php

namespace App\Support\Tenancy;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\PermissionRegistrar;

/**
 * Holds the active organization for the current request/session.
 *
 * Foundation only: membership checks and Organization models will live in
 * App\Modules\Organizations when that module is implemented.
 */
class OrganizationContext
{
    public const SESSION_KEY = 'active_organization_id';

    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function id(): ?int
    {
        $organizationId = Session::get(self::SESSION_KEY);

        return $organizationId !== null ? (int) $organizationId : null;
    }

    /**
     * Set the active organization after server-side membership validation.
     *
     * Never accept a client-supplied organization_id without verifying that
     * the authenticated user belongs to that organization.
     */
    public function set(?int $organizationId): void
    {
        if ($organizationId === null) {
            Session::forget(self::SESSION_KEY);
            $this->permissionRegistrar->setPermissionsTeamId(null);

            return;
        }

        Session::put(self::SESSION_KEY, $organizationId);
        $this->permissionRegistrar->setPermissionsTeamId($organizationId);
    }

    public function clear(): void
    {
        $this->set(null);
    }

    /**
     * Sync Spatie team context from the session at the start of a request.
     */
    public function syncPermissionsTeam(): void
    {
        $this->permissionRegistrar->setPermissionsTeamId($this->id());
    }

    /**
     * Placeholder for future membership validation.
     *
     * @param  Authenticatable&Model  $user
     */
    public function userBelongsTo(Authenticatable $user, int $organizationId): bool
    {
        // Implemented when App\Modules\Organizations is introduced.
        unset($user, $organizationId);

        return false;
    }
}
