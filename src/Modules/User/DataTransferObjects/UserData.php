<?php

namespace Modules\User\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class UserData extends DataTransferObject
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?OrganizationData $currentOrganization,
        #[DataCollectionOf(OrganizationData::class)]
        public DataCollection $organizations,
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
                ? OrganizationData::fromModel($user->currentOrganization)
                : null,
            organizations: OrganizationData::collect(
                $user->organizations->map(
                    fn (Organization $organization): OrganizationData => OrganizationData::fromModel($organization)
                ),
                DataCollection::class
            ),
        );
    }
}
