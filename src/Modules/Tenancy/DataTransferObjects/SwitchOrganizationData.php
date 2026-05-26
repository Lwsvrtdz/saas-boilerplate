<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class SwitchOrganizationData extends DataTransferObject
{
    public function __construct(
        public ?int $organizationId,
        public ?string $slug,
    ) {
    }

    public function identifier(): string
    {
        if ($this->organizationId !== null) {
            return (string) $this->organizationId;
        }

        return (string) $this->slug;
    }
}
