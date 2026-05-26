<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class CreateOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        public string $email,
        public ?int $roleId,
        public ?string $expiresAt,
    ) {
    }
}
