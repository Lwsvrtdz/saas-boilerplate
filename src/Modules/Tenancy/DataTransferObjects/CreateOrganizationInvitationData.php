<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

class CreateOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        #[Email, Max(255)]
        public string $email,
        #[MapInputName('role_id')]
        #[Nullable, Exists('roles', 'id')]
        public ?int $roleId,
        #[MapInputName('expires_at')]
        #[Nullable, Date, After('now')]
        public ?string $expiresAt,
    ) {
    }
}
