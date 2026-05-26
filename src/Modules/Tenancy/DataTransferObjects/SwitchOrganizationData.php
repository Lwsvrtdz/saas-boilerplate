<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;

class SwitchOrganizationData extends DataTransferObject
{
    public function __construct(
        #[MapInputName('organization_id')]
        #[Nullable, RequiredWithout('slug')]
        public ?int $organizationId,
        #[Nullable, Max(255), RequiredWithout('organization_id')]
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
