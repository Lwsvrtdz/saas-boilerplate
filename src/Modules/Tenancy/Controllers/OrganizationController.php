<?php

namespace Modules\Tenancy\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Controllers\ApiController;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Requests\SwitchOrganizationRequest;
use Modules\Tenancy\Services\OrganizationContextService;
use Modules\User\Models\User;

class OrganizationController extends ApiController
{
    public function __construct(
        private readonly OrganizationContextService $organizationContextService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $organizations = $request->user()
            ?->organizations()
            ->orderBy('name')
            ->get()
            ->map(fn (Organization $organization): array => OrganizationData::fromModel($organization)->toArray())
            ->all() ?? [];

        return $this->success($organizations);
    }

    public function current(Request $request): JsonResponse
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        return $this->success(
            $organization instanceof Organization ? OrganizationData::fromModel($organization)->toArray() : null
        );
    }

    public function switch(SwitchOrganizationRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw ApiException::unauthorized();
        }

        $organization = $this->organizationContextService->switchCurrentOrganization(
            $user,
            $request->toDto()->identifier(),
        );

        return $this->success(OrganizationData::fromModel($organization)->toArray());
    }
}
