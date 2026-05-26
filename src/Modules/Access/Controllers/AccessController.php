<?php

namespace Modules\Access\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Access\DataTransferObjects\PermissionData;
use Modules\Access\DataTransferObjects\RoleData;
use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;
use Modules\Shared\Controllers\ApiController;
use Spatie\LaravelData\DataCollection;

class AccessController extends ApiController
{
    public function roles(): JsonResponse
    {
        return $this->success(
            RoleData::collect(
                Role::query()
                    ->with('permissions')
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Role $role): RoleData => RoleData::fromModel($role)),
                DataCollection::class
            )
        );
    }

    public function permissions(): JsonResponse
    {
        return $this->success(
            PermissionData::collect(
                Permission::query()
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Permission $permission): PermissionData => PermissionData::fromModel($permission)),
                DataCollection::class
            )
        );
    }
}
