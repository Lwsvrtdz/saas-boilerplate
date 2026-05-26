<?php

use Database\Seeders\AccessControlSeeder;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Access\Models\Role;
use Modules\Access\Models\RoleAssignment;
use Modules\Access\Services\AuthorizationService;
use Modules\Identity\Models\ApiToken;
use Modules\Identity\Services\ApiTokenService;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    RateLimiter::clear('127.0.0.1|limited@example.com');
});

it('can register a user with their first organization and owner access', function (): void {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'pest-suite',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('user.email', 'taylor@example.com')
        ->assertJsonPath('user.currentOrganization.slug', 'taylor-otwells-organization')
        ->assertJsonPath('organization.slug', 'taylor-otwells-organization')
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'currentOrganization', 'organizations'],
            'organization' => ['id', 'name', 'slug', 'settings'],
        ]);

    $user = User::query()->where('email', 'taylor@example.com')->firstOrFail();
    $organization = Organization::query()->where('slug', 'taylor-otwells-organization')->firstOrFail();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

    expect($user->current_organization_id)->toBe($organization->getKey())
        ->and($organization->owner_id)->toBe($user->getKey())
        ->and($user->organizations()->whereKey($organization->getKey())->exists())->toBeTrue()
        ->and(RoleAssignment::query()
            ->where('role_id', $ownerRole->getKey())
            ->where('user_id', $user->getKey())
            ->where('organization_id', $organization->getKey())
            ->exists())->toBeTrue()
        ->and(ApiToken::query()->count())->toBe(1)
        ->and(Organization::current()?->is($organization))->toBeTrue();
});

it('validates registration input', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => '',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rate limits repeated login attempts', function (): void {
    User::factory()->create([
        'email' => 'limited@example.com',
        'password' => 'password',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    $this->postJson('/api/auth/login', [
        'email' => 'limited@example.com',
        'password' => 'wrong-password',
    ])->assertTooManyRequests();
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
        ->assertJsonPath('user.email', 'owner@example.com')
        ->assertJsonPath('user.currentOrganization.slug', $organization->slug);

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
        ->assertJsonPath('organization.slug', $organization->slug)
        ->assertJsonPath('user.email', $user->email);
});

it('returns a consistent unauthorized response without an api token', function (): void {
    $this->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.')
        ->assertJsonPath('errors', []);
});

it('rejects and deletes expired api tokens', function (): void {
    $user = User::factory()->create();
    $issuedToken = app(ApiTokenService::class)->issue($user);

    $issuedToken['token']->forceFill([
        'expires_at' => now()->subMinute(),
    ])->save();

    $this->withHeader('Authorization', 'Bearer '.$issuedToken['plain_text_token'])
        ->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');

    expect(ApiToken::query()->whereKey($issuedToken['token']->getKey())->exists())->toBeFalse();
});

it('rejects legacy api tokens without an expiration timestamp', function (): void {
    $user = User::factory()->create();
    $issuedToken = app(ApiTokenService::class)->issue($user);

    $issuedToken['token']->forceFill([
        'expires_at' => null,
    ])->save();

    $this->withHeader('Authorization', 'Bearer '.$issuedToken['plain_text_token'])
        ->getJson('/api/auth/me')
        ->assertUnauthorized();

    expect(ApiToken::query()->whereKey($issuedToken['token']->getKey())->exists())->toBeFalse();
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
            'metrics' => ['users', 'organizations', 'roles'],
        ]);
});

it('blocks non admins from the admin overview', function (): void {
    $user = User::factory()->create();
    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/admin/overview')
        ->assertForbidden()
        ->assertJsonPath('message', 'Admin access is required.')
        ->assertJsonPath('errors', []);
});
