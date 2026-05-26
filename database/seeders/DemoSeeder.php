<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Models\Role;
use Modules\Access\Services\AuthorizationService;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $authorizationService = app(AuthorizationService::class);

        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Demo Owner',
                'password' => 'password',
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => 'password',
            ],
        );

        $member = User::query()->updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Demo Member',
                'password' => 'password',
            ],
        );

        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'demo-company'],
            [
                'name' => 'Demo Company',
                'owner_id' => $owner->getKey(),
                'settings' => [
                    'locale' => 'en',
                    'timezone' => 'UTC',
                ],
            ],
        );

        $this->membership($owner, $organization, 'Owner', true);
        $this->membership($admin, $organization, 'Admin', false);
        $this->membership($member, $organization, 'Member', false);

        $owner->forceFill(['current_organization_id' => $organization->getKey()])->save();
        $admin->forceFill(['current_organization_id' => $organization->getKey()])->save();
        $member->forceFill(['current_organization_id' => $organization->getKey()])->save();

        $authorizationService->assignRole($owner, Role::query()->where('slug', 'owner')->firstOrFail(), $organization);
        $authorizationService->assignRole($admin, Role::query()->where('slug', 'admin')->firstOrFail());
        $authorizationService->assignRole($member, Role::query()->where('slug', 'member')->firstOrFail(), $organization);
    }

    protected function membership(User $user, Organization $organization, string $title, bool $isOwner): void
    {
        OrganizationMembership::query()->updateOrCreate(
            [
                'organization_id' => $organization->getKey(),
                'user_id' => $user->getKey(),
            ],
            [
                'title' => $title,
                'is_owner' => $isOwner,
            ],
        );
    }
}
