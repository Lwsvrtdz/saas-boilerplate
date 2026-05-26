<?php

namespace Modules\Identity\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\User\DataTransferObjects\UserData;

class LoginResponseData extends DataTransferObject
{
    public function __construct(
        public string $token,
        public ?UserData $user,
    ) {
    }

    protected function calculateResponseStatus(Request $request): int
    {
        return Response::HTTP_OK;
    }
}
