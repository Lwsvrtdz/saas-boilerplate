<?php

namespace Modules\Identity\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Identity\DataTransferObjects\AuthUserData;
use Modules\Identity\DataTransferObjects\LoginData;
use Modules\Identity\DataTransferObjects\LoginResponseData;
use Modules\Identity\DataTransferObjects\RegisterData;
use Modules\Identity\DataTransferObjects\RegisterResponseData;
use Modules\Identity\Services\ApiTokenService;
use Modules\Identity\Services\AuthenticationService;
use Modules\Identity\Services\RegistrationService;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\User\DataTransferObjects\UserData;

class AuthController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly RegistrationService $registrationService,
        private readonly ApiTokenService $apiTokenService,
    ) {
    }

    public function register(RegisterData $data): RegisterResponseData
    {
        $payload = $this->registrationService->register($data);

        return new RegisterResponseData(
            token: $payload['plain_text_token'],
            user: UserData::fromModel($payload['user']),
            organization: OrganizationData::fromModel($payload['organization']),
        );
    }

    public function login(LoginData $data): LoginResponseData
    {
        $payload = $this->authenticationService->attempt($data);

        return new LoginResponseData(
            token: $payload['plain_text_token'],
            user: UserData::fromModel($payload['user']),
        );
    }

    public function logout(Request $request): Response
    {
        $this->apiTokenService->revokeCurrentToken($request);

        return response()->noContent();
    }

    public function me(Request $request): AuthUserData
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        return new AuthUserData(
            user: UserData::fromModel($request->user()),
            organization: $organization instanceof Organization
                ? OrganizationData::fromModel($organization)
                : null,
        );
    }
}
