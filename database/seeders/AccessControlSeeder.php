<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'Organization View', 'slug' => 'organization.view'],
            ['name' => 'Organization Manage', 'slug' => 'organization.manage'],
            ['name' => 'Users Manage', 'slug' => 'users.manage'],
            ['name' => 'Roles Manage', 'slug' => 'roles.manage'],
            ['name' => 'Settings Manage', 'slug' => 'settings.manage'],
            ['name' => 'Admin Access', 'slug' => 'admin.access'],
        ])->mapWithKeys(function (array $attributes): array {
            $permission = Permission::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes
            );

            return [$permission->slug => $permission];
        });

        $roles = [
            'admin' => [
                'attributes' => ['name' => 'Admin', 'slug' => 'admin', 'scope' => 'global'],
                'permissions' => $permissions->keys()->all(),
            ],
            'owner' => [
                'attributes' => ['name' => 'Owner', 'slug' => 'owner', 'scope' => 'organization'],
                'permissions' => ['organization.view', 'organization.manage', 'users.manage', 'roles.manage', 'settings.manage'],
            ],
            'manager' => [
                'attributes' => ['name' => 'Manager', 'slug' => 'manager', 'scope' => 'organization'],
                'permissions' => ['organization.view', 'users.manage', 'settings.manage'],
            ],
            'member' => [
                'attributes' => ['name' => 'Member', 'slug' => 'member', 'scope' => 'organization'],
                'permissions' => ['organization.view'],
            ],
        ];

        foreach ($roles as $roleDefinition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $roleDefinition['attributes']['slug']],
                $roleDefinition['attributes']
            );

            $role->permissions()->sync(
                $permissions
                    ->only($roleDefinition['permissions'])
                    ->map(fn (Permission $permission): int => $permission->getKey())
                    ->values()
                    ->all()
            );
        }
    }
}
