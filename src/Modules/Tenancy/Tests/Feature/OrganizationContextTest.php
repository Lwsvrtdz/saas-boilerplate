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
