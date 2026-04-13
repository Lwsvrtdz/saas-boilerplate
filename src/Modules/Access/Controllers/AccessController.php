<?php

namespace Modules\Access\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Access\DataTransferObjects\PermissionData;
use Modules\Access\DataTransferObjects\RoleData;
use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;
use Modules\Shared\Controllers\ApiController;

class AccessController extends ApiController
{
    public function roles(): JsonResponse
    {
        return $this->success(
            Role::query()
                ->with('permissions')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role): array => RoleData::fromModel($role)->toArray())
                ->all()
        );
    }

    public function permissions(): JsonResponse
    {
        return $this->success(
            Permission::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Permission $permission): array => PermissionData::fromModel($permission)->toArray())
                ->all()
        );
    }
}
