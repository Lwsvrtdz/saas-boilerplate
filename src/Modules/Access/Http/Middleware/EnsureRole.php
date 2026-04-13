<?php

namespace Modules\Access\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Access\Services\AuthorizationService;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\Models\Organization;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        $organization = $request->attributes->get('current_organization');

        if ($user === null) {
            throw ApiException::unauthorized();
        }

        if (! $this->authorizationService->userHasRole(
            $user,
            $role,
            $organization instanceof Organization ? $organization : null
        )) {
            throw ApiException::forbidden("Missing role [{$role}].");
        }

        return $next($request);
    }
}
