<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Identity\DataTransferObjects\LoginData;
use Modules\Shared\Exceptions\ApiException;
use Modules\User\Models\User;

class AuthenticationService
{
    /**
     * @return array{plain_text_token: string, user: User}
     */
    public function attempt(LoginData $data): array
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $data->email)->first();

        if ($user === null || ! Hash::check($data->password, $user->password)) {
            throw ApiException::unauthorized('The provided credentials are invalid.');
        }

        $issuedToken = app(ApiTokenService::class)->issue($user, $data->deviceName);

        return [
            'plain_text_token' => $issuedToken['plain_text_token'],
            'user' => $user->fresh(['currentOrganization', 'organizations']),
        ];
    }
}
