<?php

namespace Modules\Identity\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class LoginData extends DataTransferObject
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName = 'nuxt-client',
    ) {
    }
}
