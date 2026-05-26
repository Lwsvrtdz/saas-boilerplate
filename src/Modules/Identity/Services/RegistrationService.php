<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Access\Models\Role;
use Modules\Access\Services\AuthorizationService;
use Modules\Identity\DataTransferObjects\RegisterData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

class RegistrationService
{
    public function __construct(
        private readonly ApiTokenService $apiTokenService,
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    /**
     * @return array{plain_text_token: string, user: User, organization: Organization}
     */
    public function register(RegisterData $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
            ]);

            $organization = Organization::query()->create([
                'name' => $this->defaultOrganizationName($data->name),
                'slug' => $this->uniqueOrganizationSlug($data->name),
                'owner_id' => $user->getKey(),
                'settings' => [
                    'locale' => 'en',
                    'timezone' => 'UTC',
                ],
            ]);

            OrganizationMembership::query()->create([
                'organization_id' => $organization->getKey(),
                'user_id' => $user->getKey(),
                'title' => 'Owner',
                'is_owner' => true,
            ]);

            $user->forceFill([
                'current_organization_id' => $organization->getKey(),
            ])->save();

            $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

            $this->authorizationService->assignRole($user, $ownerRole, $organization);

            $organization->makeCurrent();
            $issuedToken = $this->apiTokenService->issue($user, $data->deviceName);

            return [
                'plain_text_token' => $issuedToken['plain_text_token'],
                'user' => $user->fresh(['currentOrganization', 'organizations']),
                'organization' => $organization,
            ];
        });
    }

    protected function defaultOrganizationName(string $userName): string
    {
        return "{$userName}'s Organization";
    }

    protected function uniqueOrganizationSlug(string $userName): string
    {
        $baseSlug = Str::slug($this->defaultOrganizationName($userName));

        if ($baseSlug === '') {
            $baseSlug = 'organization';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
