<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\OrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restores the active organization context for permission checks.
 *
 * Does not accept organization_id from the request. Switching organizations
 * must go through a dedicated, membership-validated action.
 */
class SetOrganizationContext
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->organizationContext->syncPermissionsTeam();

        return $next($request);
    }
}
