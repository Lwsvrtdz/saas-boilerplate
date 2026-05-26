<?php

namespace Modules\Identity\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Identity\DataTransferObjects\LoginData;
use Modules\Identity\DataTransferObjects\RegisterData;
use Modules\Identity\Services\ApiTokenService;
use Modules\Identity\Services\AuthenticationService;
use Modules\Identity\Services\RegistrationService;
use Modules\Shared\Controllers\ApiController;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\User\DataTransferObjects\UserData;

class AuthController extends ApiController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly RegistrationService $registrationService,
        private readonly ApiTokenService $apiTokenService,
    ) {
    }

    public function register(RegisterData $data): JsonResponse
    {
        $payload = $this->registrationService->register($data);

        return $this->created([
            'token' => $payload['plain_text_token'],
            'user' => UserData::fromModel($payload['user']),
            'organization' => OrganizationData::fromModel($payload['organization']),
        ], 'Registered.');
    }

    public function login(LoginData $data): JsonResponse
    {
        $payload = $this->authenticationService->attempt($data);

        return $this->success([
            'token' => $payload['plain_text_token'],
            'user' => UserData::fromModel($payload['user']),
        ], 'Authenticated.');
    }

    public function logout(Request $request): Response
    {
        $this->apiTokenService->revokeCurrentToken($request);

        return $this->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        return $this->success([
            'user' => UserData::fromModel($request->user()),
            'organization' => $organization instanceof Organization
                ? OrganizationData::fromModel($organization)
                : null,
        ]);
    }
}
