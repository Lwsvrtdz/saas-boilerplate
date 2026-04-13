<?php

namespace Modules\Access\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Access\Services\AuthorizationService;
use Modules\Shared\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $this->authorizationService->userHasPermission($user, 'admin.access')) {
            throw ApiException::forbidden('Admin access is required.');
        }

        return $next($request);
    }
}
