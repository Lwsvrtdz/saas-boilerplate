<?php

namespace Modules\Identity\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Identity\Requests\LoginRequest;
use Modules\Identity\Requests\RegisterRequest;
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

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->registrationService->register($request->toDto());

        return $this->created([
            'token' => $payload['plain_text_token'],
            'user' => UserData::fromModel($payload['user'])?->toArray(),
            'organization' => OrganizationData::fromModel($payload['organization'])->toArray(),
        ], 'Registered.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authenticationService->attempt($request->toDto());

        return $this->success([
            'token' => $payload['plain_text_token'],
            'user' => UserData::fromModel($payload['user'])?->toArray(),
        ], 'Authenticated.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->apiTokenService->revokeCurrentToken($request);

        return $this->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        return $this->success([
            'user' => UserData::fromModel($request->user())?->toArray(),
            'organization' => $organization instanceof Organization
                ? [
                    'id' => $organization->getKey(),
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                ]
                : null,
        ]);
    }
}
