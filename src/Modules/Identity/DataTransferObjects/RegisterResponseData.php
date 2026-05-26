<?php

namespace Modules\Identity\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\User\DataTransferObjects\UserData;

class RegisterResponseData extends DataTransferObject
{
    public function __construct(
        public string $token,
        public ?UserData $user,
        public OrganizationData $organization,
    ) {
    }
}
