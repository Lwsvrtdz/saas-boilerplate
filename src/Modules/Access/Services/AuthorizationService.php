<?php

namespace Modules\Access\Services;

use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;
use Modules\Access\Models\RoleAssignment;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;

class AuthorizationService
{
    public function userHasRole(User $user, string $roleSlug, ?Organization $organization = null): bool
    {
        return RoleAssignment::query()
            ->where('user_id', $user->getKey())
            ->when(
                $organization !== null,
                fn ($query) => $query->where(function ($scopedQuery) use ($organization): void {
                    $scopedQuery
                        ->whereNull('organization_id')
                        ->orWhere('organization_id', $organization->getKey());
                }),
                fn ($query) => $query->whereNull('organization_id')
            )
            ->whereHas('role', fn ($query) => $query->where('slug', $roleSlug))
            ->exists();
    }

    public function userHasPermission(User $user, string $permissionSlug, ?Organization $organization = null): bool
    {
        return Permission::query()
            ->where('slug', $permissionSlug)
            ->whereHas('roles.assignments', function ($query) use ($user, $organization): void {
                $query->where('user_id', $user->getKey());

                if ($organization !== null) {
                    $query->where(function ($scopedQuery) use ($organization): void {
                        $scopedQuery
                            ->whereNull('organization_id')
                            ->orWhere('organization_id', $organization->getKey());
                    });
                } else {
                    $query->whereNull('organization_id');
                }
            })
            ->exists();
    }

    public function assignRole(User $user, Role $role, ?Organization $organization = null): RoleAssignment
    {
        return RoleAssignment::query()->firstOrCreate([
            'role_id' => $role->getKey(),
            'user_id' => $user->getKey(),
            'organization_id' => $organization?->getKey(),
        ]);
    }
}
