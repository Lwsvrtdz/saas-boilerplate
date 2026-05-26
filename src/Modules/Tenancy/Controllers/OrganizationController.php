<?php

namespace Modules\Tenancy\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Controllers\ApiController;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\DataTransferObjects\SwitchOrganizationData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Services\OrganizationContextService;
use Modules\User\Models\User;
use Spatie\LaravelData\DataCollection;

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
            ->get();

        return $this->success(
            OrganizationData::collect(
                ($organizations ?? collect())->map(
                    fn (Organization $organization): OrganizationData => OrganizationData::fromModel($organization)
                ),
                DataCollection::class
            )
        );
    }

    public function current(Request $request): JsonResponse
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        return $this->success(
            $organization instanceof Organization ? OrganizationData::fromModel($organization) : null
        );
    }

    public function switch(SwitchOrganizationData $data, Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw ApiException::unauthorized();
        }

        $organization = $this->organizationContextService->switchCurrentOrganization(
            $user,
            $data->identifier(),
        );

        return $this->success(OrganizationData::fromModel($organization));
    }
}
