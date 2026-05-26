<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class CreatedOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        public OrganizationInvitationData $invitation,
        public string $token,
    ) {
    }
}
