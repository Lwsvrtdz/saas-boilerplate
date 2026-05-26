<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\Validation\Size;

class AcceptOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        #[Size(64)]
        public string $token,
    ) {
    }
}
