<?php

use Database\Seeders\AccessControlSeeder;
use Modules\Access\Models\Role;
use Modules\Access\Services\AuthorizationService;
use Modules\Identity\Models\ApiToken;
use Modules\Identity\Services\ApiTokenService;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
});

it('can log in and receive the authenticated user payload', function (): void {
    $user = User::factory()->create([
        'email' => 'owner@example.com',
        'password' => 'password',
    ]);

    $organization = Organization::factory()->create(['owner_id' => $user->getKey()]);

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Owner',
        'is_owner' => true,
    ]);

    $user->forceFill(['current_organization_id' => $organization->getKey()])->save();

    $response = $this->postJson('/api/auth/login', [
        'email' => 'owner@example.com',
        'password' => 'password',
        'device_name' => 'pest-suite',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.user.email', 'owner@example.com')
        ->assertJsonPath('data.user.currentOrganization.slug', $organization->slug);

    expect(ApiToken::query()->count())->toBe(1);
});

it('returns the current authenticated user and organization context', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->getKey()]);

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    $user->forceFill(['current_organization_id' => $organization->getKey()])->save();

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->withHeader('X-Organization', $organization->slug)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.organization.slug', $organization->slug)
        ->assertJsonPath('data.user.email', $user->email);
});

it('allows admins to access the admin overview', function (): void {
    $user = User::factory()->create();
    $role = Role::query()->where('slug', 'admin')->firstOrFail();

    app(AuthorizationService::class)->assignRole($user, $role);

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/admin/overview')
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'metrics' => ['users', 'organizations', 'roles'],
            ],
            'meta',
        ]);
});

it('blocks non admins from the admin overview', function (): void {
    $user = User::factory()->create();
    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/admin/overview')
        ->assertForbidden();
});
