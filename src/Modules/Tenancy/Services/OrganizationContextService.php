<?php

namespace Modules\Tenancy\Services;

use Illuminate\Http\Request;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;

class OrganizationContextService
{
    public function resolveForRequest(Request $request): ?Organization
    {
        $identifier = $this->resolveIdentifier($request);
        $user = $request->user();

        if ($identifier === null) {
            if (! $user instanceof User || $user->current_organization_id === null) {
                return null;
            }

            return $this->resolveForUser($user, (string) $user->current_organization_id);
        }

        if (! $user instanceof User) {
            return null;
        }

        return $this->resolveForUser($user, $identifier);
    }

    protected function resolveIdentifier(Request $request): ?string
    {
        $headerName = (string) config('boilerplate.organization_header', 'X-Organization');
        $routeValue = $request->route('organization');
        $headerValue = $request->header($headerName);

        if (is_string($routeValue) && $routeValue !== '') {
            return $routeValue;
        }

        if (is_string($headerValue) && $headerValue !== '') {
            return $headerValue;
        }

        return null;
    }

    protected function resolveForUser(User $user, string $identifier): ?Organization
    {
        $organization = Organization::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('slug', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhereKey((int) $identifier);
                }
            })
            ->first();

        if ($organization === null) {
            throw ApiException::notFound('Organization context was not found.');
        }

        $belongsToOrganization = $user->organizations()
            ->whereKey($organization->getKey())
            ->exists();

        if (! $belongsToOrganization) {
            throw ApiException::forbidden('You do not belong to the requested organization.');
        }

        return $organization;
    }
}
