<?php

namespace Modules\Tenancy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Services\OrganizationContextService;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizationContext
{
    public function __construct(
        private readonly OrganizationContextService $organizationContextService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        Organization::forgetCurrent();

        $organization = $this->organizationContextService->resolveForRequest($request);

        if ($organization !== null) {
            $organization->makeCurrent();
            $request->attributes->set('current_organization', $organization);
        }

        return $next($request);
    }
}
