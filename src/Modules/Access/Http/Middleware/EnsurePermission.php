<?php

namespace Modules\Access\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Access\Services\AuthorizationService;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\Models\Organization;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $organization = $request->attributes->get('current_organization');

        if ($user === null) {
            throw ApiException::unauthorized();
        }

        if (! $this->authorizationService->userHasPermission(
            $user,
            $permission,
            $organization instanceof Organization ? $organization : null
        )) {
            throw ApiException::forbidden("Missing permission [{$permission}].");
        }

        return $next($request);
    }
}
