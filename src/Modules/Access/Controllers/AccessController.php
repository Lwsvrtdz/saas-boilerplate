<?php

namespace Modules\Access\Controllers;

use Modules\Access\DataTransferObjects\PermissionData;
use Modules\Access\DataTransferObjects\RoleData;
use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;
use Spatie\LaravelData\DataCollection;

class AccessController
{
    public function roles(): DataCollection
    {
        return RoleData::collect(
            Role::query()
                ->with('permissions')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role): RoleData => RoleData::fromModel($role)),
            DataCollection::class
        );
    }

    public function permissions(): DataCollection
    {
        return PermissionData::collect(
            Permission::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Permission $permission): PermissionData => PermissionData::fromModel($permission)),
            DataCollection::class
        );
    }
}
