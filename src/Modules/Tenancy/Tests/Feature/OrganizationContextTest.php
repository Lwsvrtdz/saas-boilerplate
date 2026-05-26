<?php

use Database\Seeders\AccessControlSeeder;
use Illuminate\Database\QueryException;
use Modules\Access\Models\Role;
use Modules\Access\Models\RoleAssignment;
use Modules\Access\Services\AuthorizationService;
use Modules\Identity\Services\ApiTokenService;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationInvitation;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
});

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
        ->assertJsonPath('0.slug', $organization->slug);
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
        ->assertJsonPath('slug', $organization->slug);

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
        ->assertJsonPath('slug', $secondOrganization->slug);

    expect($user->fresh()->current_organization_id)->toBe($secondOrganization->getKey())
        ->and(Organization::current()?->is($secondOrganization))->toBeTrue();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/organizations/current', [
            'slug' => $firstOrganization->slug,
        ])
        ->assertOk()
        ->assertJsonPath('slug', $firstOrganization->slug);

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

it('creates and lists organization invitations for organization managers', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
    $memberRole = Role::query()->where('slug', 'member')->firstOrFail();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Owner',
        'is_owner' => true,
    ]);

    $user->forceFill(['current_organization_id' => $organization->getKey()])->save();
    app(AuthorizationService::class)->assignRole($user, $ownerRole, $organization);

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/organizations/current/invitations', [
            'email' => 'invitee@example.com',
            'role_id' => $memberRole->getKey(),
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('invitation.email', 'invitee@example.com')
        ->assertJsonPath('invitation.roleId', $memberRole->getKey())
        ->assertJsonStructure(['token']);

    expect(hash('sha256', $response->json('token')))
        ->toBe(OrganizationInvitation::query()->firstOrFail()->token_hash);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/organizations/current/invitations')
        ->assertOk()
        ->assertJsonPath('0.email', 'invitee@example.com');
});

it('blocks users without organization management access from inviting members', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Member',
        'is_owner' => false,
    ]);

    $user->forceFill(['current_organization_id' => $organization->getKey()])->save();

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/organizations/current/invitations', [
            'email' => 'invitee@example.com',
        ])
        ->assertForbidden();

    expect(OrganizationInvitation::query()->count())->toBe(0);
});

it('deletes organization invitations for organization managers', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->getKey(),
        'user_id' => $user->getKey(),
        'title' => 'Owner',
        'is_owner' => true,
    ]);

    $user->forceFill(['current_organization_id' => $organization->getKey()])->save();
    app(AuthorizationService::class)->assignRole($user, $ownerRole, $organization);

    $invitation = OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', str_repeat('a', 64)),
        'invited_by_user_id' => $user->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDay(),
    ]);

    $token = app(ApiTokenService::class)->issue($user)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson("/api/organizations/current/invitations/{$invitation->getKey()}")
        ->assertNoContent();

    expect(OrganizationInvitation::query()->count())->toBe(0);
});

it('accepts an invitation when the authenticated user email matches', function (): void {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    $organization = Organization::factory()->create();
    $memberRole = Role::query()->where('slug', 'member')->firstOrFail();
    $plainTextInvitationToken = str_repeat('b', 64);

    $invitation = OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'role_id' => $memberRole->getKey(),
        'token_hash' => hash('sha256', $plainTextInvitationToken),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDay(),
    ]);

    $token = app(ApiTokenService::class)->issue($invitee)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/invitations/accept', [
            'token' => $plainTextInvitationToken,
        ])
        ->assertOk()
        ->assertJsonPath('organization.slug', $organization->slug);

    expect($invitee->organizations()->whereKey($organization->getKey())->exists())->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull()
        ->and(RoleAssignment::query()
            ->where('role_id', $memberRole->getKey())
            ->where('user_id', $invitee->getKey())
            ->where('organization_id', $organization->getKey())
            ->exists())->toBeTrue();
});

it('blocks accepting an invitation for a different email address', function (): void {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'other@example.com']);
    $organization = Organization::factory()->create();
    $plainTextInvitationToken = str_repeat('c', 64);

    OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', $plainTextInvitationToken),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDay(),
    ]);

    $token = app(ApiTokenService::class)->issue($invitee)['plain_text_token'];

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/invitations/accept', [
            'token' => $plainTextInvitationToken,
        ])
        ->assertForbidden();

    expect($invitee->organizations()->whereKey($organization->getKey())->exists())->toBeFalse();
});

it('enforces pending invitation uniqueness at the database level', function (): void {
    $inviter = User::factory()->create();
    $organization = Organization::factory()->create();

    OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', str_repeat('d', 64)),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDay(),
    ]);

    OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', str_repeat('e', 64)),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDays(2),
    ]);
})->throws(QueryException::class);

it('allows a new pending invitation after an earlier one is accepted', function (): void {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    $organization = Organization::factory()->create();
    $plainTextInvitationToken = str_repeat('f', 64);

    $invitation = OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', $plainTextInvitationToken),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDay(),
    ]);

    app(\Modules\Tenancy\Services\OrganizationInvitationService::class)->accept(
        $invitee,
        new \Modules\Tenancy\DataTransferObjects\AcceptOrganizationInvitationData($plainTextInvitationToken),
    );

    $replacementInvitation = OrganizationInvitation::query()->create([
        'organization_id' => $organization->getKey(),
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', str_repeat('g', 64)),
        'invited_by_user_id' => $inviter->getKey(),
        'pending_marker' => true,
        'expires_at' => now()->addDays(2),
    ]);

    expect($invitation->fresh()?->accepted_at)->not->toBeNull()
        ->and($invitation->fresh()?->pending_marker)->toBeNull()
        ->and($replacementInvitation->pending_marker)->toBeTrue();
});
