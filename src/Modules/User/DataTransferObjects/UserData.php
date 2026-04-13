<?php

namespace Modules\User\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;

class UserData extends DataTransferObject
{
    /**
     * @param array<int, array<string, mixed>> $organizations
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?array $currentOrganization,
        public array $organizations,
    ) {
    }

    public static function fromModel(?User $user): ?self
    {
        if ($user === null) {
            return null;
        }

        $user->loadMissing(['currentOrganization', 'organizations']);

        return new self(
            id: $user->getKey(),
            name: $user->name,
            email: $user->email,
            currentOrganization: $user->currentOrganization instanceof Organization
                ? OrganizationData::fromModel($user->currentOrganization)->toArray()
                : null,
            organizations: $user->organizations
                ->map(fn (Organization $organization): array => OrganizationData::fromModel($organization)->toArray())
                ->all(),
        );
    }
}
