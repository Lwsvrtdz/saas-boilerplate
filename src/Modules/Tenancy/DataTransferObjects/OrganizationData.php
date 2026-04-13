<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\Tenancy\Models\Organization;

class OrganizationData extends DataTransferObject
{
    /**
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?array $settings,
    ) {
    }

    public static function fromModel(Organization $organization): self
    {
        return new self(
            id: $organization->getKey(),
            name: $organization->name,
            slug: $organization->slug,
            settings: $organization->settings,
        );
    }
}
