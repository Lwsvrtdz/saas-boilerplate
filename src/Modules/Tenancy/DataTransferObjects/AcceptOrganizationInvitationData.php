<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class AcceptOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        public string $token,
    ) {
    }
}
