<?php

use Modules\Identity\Services\ApiTokenService;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

it('lists the organizations available to the authenticated user', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me/organizations')
        ->assertOk()
        ->assertJsonPath('data.0.slug', $organization->slug);
});

it('sets the resolved organization as the current spatie tenant', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->withHeader('X-Organization', $organization->slug)
        ->getJson('/api/organizations/current')
        ->assertOk()
        ->assertJsonPath('data.slug', $organization->slug);

    expect(Organization::current()?->is($organization))->toBeTrue();
});

it('switches the current organization by id and slug', function (): void {
    $user = User::factory()->create();
    $firstOrganization = Organization::factory()->create();
    $secondOrganization = Organization::factory()->create();

    OrganizationMembership::query()->create([
        'organization_id' => $firstOrganization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    OrganizationMembership::query()->create([
        'organization_id' => $secondOrganization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    $user->forceFill(['current_organization_id' => $firstOrganization->getKey()])->save();

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/organizations/current', [
            'organization_id' => $secondOrganization->getKey(),
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', $secondOrganization->slug);

    expect($user->fresh()->current_organization_id)->toBe($secondOrganization->getKey())
        ->and(Organization::current()?->is($secondOrganization))->toBeTrue();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/organizations/current', [
            'slug' => $firstOrganization->slug,
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', $firstOrganization->slug);

    expect($user->fresh()->current_organization_id)->toBe($firstOrganization->getKey())
        ->and(Organization::current()?->is($firstOrganization))->toBeTrue();
});

it('blocks switching to an organization the user does not belong to', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/organizations/current', [
            'slug' => $organization->slug,
        ])
        ->assertForbidden();

    expect($user->fresh()->current_organization_id)->toBeNull();
});

it('returns not found when switching to a missing organization', function (): void {
    $user = User::factory()->create();
    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/organizations/current', [
            'organization_id' => 999999,
        ])
        ->assertNotFound();

    expect($user->fresh()->current_organization_id)->toBeNull();
});
